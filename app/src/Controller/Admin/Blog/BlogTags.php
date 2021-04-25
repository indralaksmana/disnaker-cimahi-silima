<?php

namespace App\Controller\Admin\Blog;

use Carbon\Carbon;
use App\Library\VideoParser as VP;
use App\Model\BlogCategories;
use App\Model\BlogTags as BT;
use App\Model\BlogPosts;
use App\Model\BlogPostsComments;
use App\Model\BlogPostsReplies;
use App\Model\BlogPostsTags;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class BlogTags extends Controller
{
    // Add New Blog Tag
    public function tagsAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_tags.create', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            $tagName = $request->getParam('tag_name');
            $tagSlug = $request->getParam('tag_slug');

            $this->validator->validate($request, [
                'tag_name' => V::length(2, 25)->alpha('\''),
                'tag_slug' => V::slug()
            ]);

            $checkSlug = BT::where('slug', '=', $request->getParam('tag_slug'))->get()->count();

            if ($checkSlug > 0) {
                $this->validator->addError('tag_slug', 'Slug already in use.');
            }

            if ($this->validator->isValid()) {
                $addTag = new BT;
                $addTag->name = $tagName;
                $addTag->slug = $tagSlug;

                if ($addTag->save()) {
                    $this->flash('success', 'Tags Baru Berhasil Di Tambahkan');
                    return $this->redirect($response, 'admin-blog-tags-add');
                }


            }
            $this->flash('danger', 'Tags Baru Gagal di tambahkan');

        }
        return $this->view->render(
          $response, 'dashboard/berita/tags.twig',
          array(
            "tags" => BT::get()
          )
        );
    }

    // Delete Blog Tag
    public function tagsDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_tags.delete', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $tag = BT::find($request->getParam('tag_id'));


        if (!$tag) {
            $this->flash('danger', 'Tag doesn\'t exist.');
            return $this->redirect($response, 'admin-blog-tags-add');
        }

        if ($tag->delete()) {
            $this->flash('success', 'Tag has been removed.');
            return $this->redirect($response, 'admin-blog-tags-add');
        }

        $this->flash('danger', 'There was a problem removing the tag.');
        return $this->redirect($response, 'admin-blog-tags-add');
    }

    // Edit Blog Tag
    public function tagsEdit(Request $request, Response $response, $tagId)
    {
        if ($check = $this->sentinel->hasPerm('blog_tags.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $tag = BT::find($tagId);

        if (!$tag) {
            $this->flash('danger', 'Tag doesn\'t exist.');
            return $this->redirect($response, 'admin-blog-tags-add');
        }

        if ($request->isPost()) {
            // Get Vars
            $tagName = $request->getParam('tag_name');
            $tagSlug = $request->getParam('tag_slug');

            // Validate Data
            $validateData = array(
                'tag_name' => array(
                    'rules' => V::length(2, 25)->alpha('\''),
                    'messages' => array(
                        'length' => 'Must be between 2 and 25 characters.',
                        'alpha' => 'Letters only and can contain \''
                        )
                ),
                'tag_slug' => array(
                    'rules' => V::slug(),
                    'messages' => array(
                        'slug' => 'May only contain lowercase letters, numbers and hyphens.'
                        )
                )
            );

            $this->validator->validate($request, $validateData);

            //Validate Category Slug
            $checkSlug = $tag->where('id', '!=', $tagId)->where('slug', '=', $tagSlug)->get()->count();
            if ($checkSlug > 0 && $tagSlug != $tag['slug']) {
                $this->validator->addError('tag_slug', 'Category slug is already in use.');
            }


            if ($this->validator->isValid()) {
                $tag->name = $tagName;
                $tag->slug = $tagSlug;

                if ($tag->save()) {
                    $this->flash('success', 'Tags Berhasil di edit');
                    return $this->redirect($response, 'admin-blog-tags-add');
                }

                $this->flash('success', 'Tags Gagal di edit');
            }
        }
        return $this->view->render($response, 'dashboard/berita/berita-tags-edit.twig', ['tag' => $tag]);
    }
}
