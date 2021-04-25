<?php

namespace App\Controller;

use Carbon\Carbon;
use App\Model\JobCategories;
use App\Model\JobPosts;
use App\Model\JobPostsComments;

use App\Model\JobTags;
use App\Model\Users;
use App\Library\Paginator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Job extends Controller
{

    // Main job Page
    public function job(Request $request, Response $response)
    {

        // Get Page Number
        $page = 1;
        $routeArgs =  $request->getAttribute('route')->getArguments();
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $posts = JobPosts::where('status', 1)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'author')
            ->withCount('comments', 'pendingComments')
            ->orderBy('publish_at', 'DESC');
     
        $pagination = new Paginator($posts->count(), $this->config['job-per-page'], $page, "/berita/(:num)");
        $pagination = $pagination;

        $posts = $posts->skip($this->config['job-per-page']*($page-1))
            ->take($this->config['job-per-page']);
        
        return $this->view->render(
            $response,
            'disnaker/job.twig',
            array("posts" => $posts->get(), "pagination" => $pagination)
        );
    }

    // Author Posts Page
    public function jobAuthor(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkAuthor = Users::where('username', $routeArgs['username'])->first();

        if (!$checkAuthor) {
            $this->flash('warning', 'Author not found.');
            return $this->redirect($response, 'job');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $posts = JobPosts::where('status', 1)
            ->where('user_id', $checkAuthor->id)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'author')
            ->withCount('comments', 'pendingComments')
            ->orderBy('publish_at', 'DESC');
       
        $pagination = new Paginator(
            $posts->count(),
            $this->config['job-per-page'],
            $page,
            "/job/author/".$checkAuthor->username."/(:num)"
        );
        $pagination = $pagination;

        $posts = $posts->skip($this->config['job-per-page']*($page-1))
            ->take($this->config['job-per-page']);

        return $this->view->render(
            $response,
            'disnaker/job.twig',
            array(
                "author" => $checkAuthor,
                "posts" => $posts->get(),
                "pagination" => $pagination,
                "authorPage" => true
            )
        );
    }

    // Category Posts Page
    public function jobCategory(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkCat = JobCategories::where('slug', $routeArgs['slug'])->first();

        if (!$checkCat) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'job');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $posts = JobCategories::withCount(['posts' => function ($query) {
            $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now());
        }])
            ->with(['posts' => function ($query) use ($page) {
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now())
                    ->with('category', 'tags', 'author')
                    ->withCount('comments', 'pendingComments')
                    ->skip($this->config['job-per-page']*($page-1))
                    ->take($this->config['job-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($checkCat->id);

       
        $pagination = new Paginator(
            $posts->posts_count,
            $this->config['job-per-page'],
            $page,
            "/job/category/".$checkCat->slug."/(:num)"
        );
        $pagination = $pagination;

        return $this->view->render(
            $response,
            'disnaker/job.twig',
            array(
                "category" => $checkCat,
                "posts" => $posts->posts,
                "pagination" => $pagination,
                "categoryPage" => true
            )
        );
    }

    // job Post
    public function jobPost(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();

        $post = JobPosts::with(
            'tags',
            'category',
            'author',
            'author.profile',
            'approvedComments',
            'approvedComments.approvedReplies'
        )
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->where('publish_at', '<', Carbon::now())
            ->first();
        
        if (!$post) {
            $this->flash('danger', 'That job post cound not be found.');
            return $this->redirect($response, 'job');
        }

        if ($request->isPost()) {
            if (!$this->auth->check()) {
                $this->flashNow('danger', 'You need to be logged in to comment.');
                return $this->view->render($response, 'job-post.twig', array("post" => $post, "showSidebar" => 1));
            }
            
            if ($request->getParam('add_comment') !== null) {
                if ($this->addComment($post)) {
                    $this->flash('success', 'Your comment has been submitted.');
                    return $response->withRedirect($request->getUri()->getPath());
                }
                $this->flashNow('danger', 'There was a problem submitting your comment. please try again.');
            }

            if ($request->getParam('add_reply') !== null) {
                if ($this->addReply()) {
                    $this->flash('success', 'Your reply has been submitted.');
                    return $response->withRedirect($request->getUri()->getPath());
                }
                $this->flashNow('danger', 'There was a problem submitting your reply. please try again.');
            }
        }

        return $this->view->render(
            $response,
            'disnaker/job-post.twig',
            array("post" => $post, "showSidebar" => 1, "requestParams" => $request->getParams())
        );
    }

    private function addReply()
    {
        // Validate Data
        $validateData = array(
            'reply' => array(
                'rules' => V::notEmpty()->length(6),
                'messages' => array(
                    'notEmpty' => 'Please enter a comment.',
                    'length' => 'Comment must contain at least 6 characters'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        // Validate Comment
        $comment = JobPostsComments::find($this->request->getParam('comment_id'));

        if (!$comment) {
            return false;
        }

        if ($this->validator->isValid()) {
            $addReply = new JobPostsReplies;
            $addReply->user_id = $this->auth->check()->id;
            $addReply->comment_id = $comment->id;
            $addReply->reply = strip_tags($this->request->getParam('reply'));
            $addReply->status = 1;
            if ($this->config['job-approve-comments']) {
                $addReply->status = 0;
            }
            if ($addReply->save()) {
                return true;
            }
        }
        return false;
    }

    private function addComment($post)
    {
        // Validate Data
        $validateData = array(
            'comment' => array(
                'rules' => V::notEmpty()->length(6),
                'messages' => array(
                    'notEmpty' => 'Please enter a comment.',
                    'length' => 'Comment must contain at least 6 characters'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        if ($this->validator->isValid()) {
            $addComment = new JobPostsComments;
            $addComment->user_id = $this->auth->check()->id;
            $addComment->post_id = $post->id;
            $addComment->comment = strip_tags($this->request->getParam('comment'));
            $addComment->status = 1;
            if ($this->config['job-approve-comments']) {
                $addComment->status = 0;
            }
            if ($addComment->save()) {
                return true;
            }
        }
        return false;
    }


    // Tag posts page
    public function jobTag(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkTag = JobTags::where('slug', $routeArgs['slug'])->first();

        if (!$checkTag) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'job');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        if (isset($routeArgs['page']) && !is_numeric($routeArgs['page'])) {
            $this->flash('warning', 'Page not found.');
            return $this->redirect($response, 'job');
        }

        $posts = JobTags::withCount(['posts' => function ($query) {
            $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now());
        }])
            ->with(['posts' => function ($query) use ($page) {
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now())
                    ->with('category', 'tags', 'author')
                    ->withCount('comments', 'pendingComments')
                    ->skip($this->config['job-per-page']*($page-1))
                    ->take($this->config['job-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($checkTag->id);

       
        $pagination = new Paginator(
            $posts->posts_count,
            $this->config['job-per-page'],
            $page,
            "/berita/tag/".$checkTag->slug."/(:num)"
        );
        $pagination = $pagination;

        return $this->view->render(
            $response,
            'disnaker/job.twig',
            array(
                "tag" => $checkTag,
                "posts" => $posts->posts,
                "pagination" => $pagination,
                "tagPage" => true
            )
        );
    }
}
