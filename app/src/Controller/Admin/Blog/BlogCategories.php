<?php


namespace App\Controller\Admin\Blog;

use Carbon\Carbon;
use App\Library\VideoParser as VP;
use App\Model\BlogCategories as BC;
use App\Model\BlogTags;
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
class BlogCategories extends Controller
{
    // Add New Blog Category
    public function categoriesAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_categories.create', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            $this->validator->validate($request, [
                'category_name' => V::length(2, 25)->alpha('\''),
                'category_slug' => V::slug()
            ]);

            $checkSlug = BC::where('slug', '=', $request->getParam('category_slug'))->get()->count();

            if ($checkSlug > 0) {
                $this->validator->addError('category_slug', 'Sudah Di pake.');
            }

            if ($this->validator->isValid()) {
                $addCategory = new BC;
                $addCategory->name = $request->getParam('category_name');
                $addCategory->slug = $request->getParam('category_slug');

                if ($addCategory->save()) {
                    $this->flash('success', 'Kategori Baru Berhasil di tambahkan.');
                    return $this->redirect($response, 'admin-blog-categories-add');
                }
            }
            $this->flash('danger', 'Kategori Baru Gagal di tambahkan.');
        }
        return $this->view->render(
          $response,
          'dashboard/berita/kategori.twig',
          array(
              "categories" => BC::get()
          )
        );
    }

    // Delete Blog Category
    public function categoriesDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_categories.delete', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $category = BC::find($request->getParam('category_id'));

        if (!$category) {
            $this->flash('danger', 'Kategori Tidak ada');
            return $this->redirect($response, 'admin-blog-categories-add');
        }

        if ($category->delete()) {
            $this->flash('success', 'Kategori Berhasil di Hapus.');
            return $this->redirect($response, 'admin-blog-categories-add');
        }

        $this->flash('danger', 'Kategori Gagal di Hapus.');
        return $this->redirect($response, 'admin-blog-categories-add');
    }

    // Edit Blog Category
    public function categoriesEdit(Request $request, Response $response, $categoryId)
    {
        if ($check = $this->sentinel->hasPerm('blog_categories.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $category = BC::find($categoryId);

        if (!$category) {
            $this->flash('danger', 'Sorry, that category was not found.');
            return $response->withRedirect($this->router->pathFor('admin-blog'));
        }

        if ($request->isPost()) {
            // Get Vars
            $categoryName = $request->getParam('category_name');
            $categorySlug = $request->getParam('category_slug');

            // Validate Data
            $validateData = array(
                'category_name' => array(
                    'rules' => V::length(2, 25)->alpha('\''),
                    'messages' => array(
                        'length' => 'Must be between 2 and 25 characters.',
                        'alpha' => 'Letters only and can contain \''
                        )
                ),
                'category_slug' => array(
                    'rules' => V::slug(),
                    'messages' => array(
                        'slug' => 'May only contain lowercase letters, numbers and hyphens.'
                        )
                )
            );

            $this->validator->validate($request, $validateData);

            // Validate Category Slug
            $checkSlug = $category->where('id', '!=', $category->id)
                ->where('slug', '=', $categorySlug)
                ->get()
                ->count();
            if ($checkSlug > 0 && $categorySlug != $category->slug) {
                $this->validator->addError('category_slug', 'Category slug is already in use.');
            }


            if ($this->validator->isValid()) {
                if ($category->id == 1) {
                    $this->flash('danger', 'Cannot edit uncategorized category.');
                    return $this->redirect($response, 'admin-blog-categories-add');
                }

                $category->name = $categoryName;
                $category->slug = $categorySlug;

                if ($category->save()) {
                    $this->flash('success', 'Kategori Berhasil di edit');
                    return $this->redirect($response, 'admin-blog-categories-add');
                }
            }
            $this->flash('danger', 'Kategori Gagal di edit.');
        }

        return $this->view->render($response, 'dashboard/berita/berita-categories-edit.twig', ['category' => $category]);
    }
}
