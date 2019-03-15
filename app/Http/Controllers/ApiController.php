<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use App\Category;
use App\Product;
use App\Brand;
use App\User;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Intervention\Image\ImageManagerStatic as Image;

class ApiController extends Controller
{
    /**
     * @param Request $request
     * @return \Psr\Http\Message\StreamInterface
     */
    public function login (Request $request)
    {
        $client = new GuzzleHttp\Client();
        try {
            $res = $client->request('POST', url('/oauth/token'), [
                'form_params' => [
                    'grant_type'    => 'password',
                    'client_id'     => env('PASSPORT_USER_ID'),
                    'client_secret' => env('PASSPORT_USER_SECRET'),
                    'username'      => $request->input('username'),
                    'password'      => $request->input('password')
                ]
            ])->getBody();
            Auth::loginUsingId(1);
            return $res;
        } catch (GuzzleException $e) {
            $data = [
                'message' => 'User unauthorized...'
            ];
            echo json_encode($data);
        }
    }

    /**
     * @param Request $request
     * @return string
     */
    public function createCategory (Request $request)
    {
        $userId = $request->user()->id;
        $categoryName = $request->post('name');

        // Create new category
        $category = new Category;
        $category->creator_id = $userId;
        $category->name = $categoryName;
        $category->save();

        User::addExperience($userId, 20);

        return json_encode(['message' => 'Category ' . $categoryName . ' for user ' . $userId . ' created!']);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function deleteCategory (Request $request)
    {
        $userId = $request->user()->id;
        $id = $request->post('id');

        // Delete category
        Category::where([
            'creator_id' => $userId,
            'id'         => $id
        ])->delete();
        Product::where('category_id', $id)->orderby('name')->delete();

        return json_encode(['message' => 'Category ' . $id . ' for user ' . $userId . ' and all products inside deleted!']);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getCategories (Request $request)
    {
        $userId = $request->user()->id;
        return Category::where('creator_id', $userId)->orderby('name')->get();
    }

    /**
     * @param Request $request
     * @return string
     */
    public function createBrand (Request $request)
    {
        $userId = $request->user()->id;
        $name = $request->post('name');

        // Create new brand
        $brand = new Brand;
        $brand->creator_id = $userId;
        $brand->name = $name;
        $brand->save();

        User::addExperience($userId, 20);

        return json_encode(['message' => 'Brand ' . $name . ' for user ' . $userId . ' created!']);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function deleteBrand (Request $request)
    {
        $userId = $request->user()->id;
        $id = $request->post('id');

        // Delete brand
        Brand::where([
            'creator_id' => $userId,
            'id'         => $id
        ])->delete();
        Product::where('brand_id', $id)->delete();

        return json_encode(['message' => 'Brand ' . $id . ' for user ' . $userId . ' and all products with belong to it deleted!']);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getBrands (Request $request)
    {
        $userId = $request->user()->id;
        return Brand::where('creator_id', $userId)->orderby('name')->get();
    }

    /**
     * @param Request $request
     * @return string
     */
    public function createProduct (Request $request)
    {
        $userId = $request->user()->id;
        $name = $request->post('name');
        $categoryId = $request->post('categoryId');
        $brandId = $request->post('brandId');
        $description = $request->post('description');
        //$photos = $request->post('photo');

        // Create new product
        $product = new Product;
        $product->creator_id = $userId;
        $product->category_id = $categoryId;
        $product->brand_id = $brandId;
        $product->name = $name;
        $product->description = $description;
        $product->photos = '';
        $product->save();

        User::addExperience($userId, 50);

        return json_encode(['message' => 'Product ' . $name . ' for category ' . $categoryId . ' created!']);
    }

    /**
     * @param $id
     * @param $field
     * @param Request $request
     * @return string
     */
    public function updateProduct ($id, $field, Request $request)
    {
        $userId = $request->user()->id;

        // Update product
        $product = Product::where([
            'creator_id' => $userId,
            'id'         => $id
        ])->firstOrFail();
        $product->$field = $request->post('value');
        $product->save();

        if ($field === 'pan') {
            if ($request->post('value') == 1) {
                User::addExperience($userId, 3);
            } else {
                User::addExperience($userId, -3);
            }
        }

        return json_encode(['message' => 'Product ' . $id . ' at ' . $field . ' updated!']);
    }

    /**
     * @param $id
     * @param Request $request
     * @return array
     */
    public function getProduct ($id, Request $request)
    {
        $userId = $request->user()->id;
        $product = Product::where([
            'creator_id' => $userId,
            'id'         => $id
        ])->firstOrFail();
        $brand = Brand::where('id', $product['brand_id'])->first();
        $category = Category::where('id', $product['category_id'])->first();
        $toReturn = [
            'id'                => $product->id,
            'name'              => $product->name,
            'description'       => $product->description,
            'first_impressions' => $product->first_impressions,
            'updates'           => $product->updates,
            'photos'            => Product::getPhotos($id),
            'remaining_amount'  => $product->remaining_amount,
            'uses_count'        => $product->uses_count,
            'last_use'          => date('d.m.Y', strtotime($product->last_use)),
            'brand'             => $brand,
            'category'          => $category,
            'rating'            => $product->rating,
            'pan'               => $product->pan,
            'bought_at'         => date('d.m.Y', strtotime($product->bought_at)),
            'expire_months'     => $product->expire_months,
            'expire_date'       => date('d.m.Y', (strtotime($product->bought_at) + 60 * 60 * 24 * 30 * $product->expire_months))
        ];

        return $toReturn;
    }

    public function useAdd ($id, Request $request)
    {
        $userId = $request->user()->id;

        $product = Product::where([
            'creator_id' => $userId,
            'id'         => $id
        ])->firstOrFail();

        $product->uses_count++;
        $product->last_use = $currentTimestamp = Carbon::now()->toDateTimeString();
        $product->save();

        User::addExperience($userId, 3);

        return json_encode(['message' => 'Use count++ for product ' . $id . '!']);
    }

    /**
     * @param $id
     * @param Request $request
     * @return string
     */
    public function addPhoto ($id, Request $request)
    {
        $userId = $request->user()->id;

        $image = $request->file('photo');
        $name = $userId . '_' . $id . '_' . str_slug($image->getClientOriginalName()) . '.' . $image->getClientOriginalExtension();
        $image->storeAs('public/products', $name);


        ImageOptimizer::optimize(base_path('storage/app/public/products/' . $name));

        // Create thumbnail
        Image::make($image->getRealPath())->resize(null, 300, function ($constraint){$constraint->aspectRatio();})->save(public_path('storage/products/thumbnail_' . $name));

        Product::addPhoto($id, $name);
        User::addExperience($userId, 9);

        return json_encode(['message' => base_path('storage/app/public/products/' . $name)]);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function deleteProduct (Request $request)
    {
        $userId = $request->user()->id;
        $id = $request->post('id');

        // Delete product
        Product::where([
            'creator_id' => $userId,
            'id'         => $id
        ])->delete();

        return json_encode(['message' => 'Product ' . $id . ' for user ' . $userId . ' deleted!']);
    }

    /**
     * @param $id
     * @param $photoIndex
     * @param Request $request
     * @return string
     */
    public function setFirstPhoto ($id, $photoIndex, Request $request)
    {
        $userId = $request->user()->id;

        $product = Product::where([
            'creator_id' => $userId,
            'id'         => $id
        ])->first();

        $newPhotos = '';

        $productPhotos = Product::getPhotos($id, true);
        //array_reverse($productPhotos);

        if ((count($productPhotos) - 1) > 0) {
            foreach ($productPhotos as $index => $photo) {
                if ($index != $photoIndex) {
                    if ($index != (count($productPhotos) - 1) || $index != 0) {
                        $newPhotos .= $photo . ';';
                    } else {
                        $newPhotos .= $photo;
                    }
                }
            }
        }
        $newPhotos .= $productPhotos[$photoIndex];

        if (!file_exists(base_path('storage/app/public/products/thumbnail_' . explode(':', $productPhotos[$photoIndex])[0]))) {

            $fileName = base_path('storage/app/public/products/' . explode(':', $productPhotos[$photoIndex])[0]);
            $file = fopen($fileName, 'r');

            Image::make($file)->resize(null, 300, function ($constraint){$constraint->aspectRatio();})->save(public_path('storage/products/thumbnail_' . explode(':', $productPhotos[$photoIndex])[0]));

        }

        $product->photos = $newPhotos;
        $product->save();

        User::addExperience($userId, 5);

        return json_encode(['message' => 'Photo of index ' . $photoIndex . ' from item ' . $id . ' set ti first position!']);
    }

    /**
     * @param $id
     * @param $photoIndex
     * @param Request $request
     * @return string
     */
    public function deleteProductPhoto ($id, $photoIndex, Request $request)
    {
        $userId = $request->user()->id;

        $product = Product::where([
            'creator_id' => $userId,
            'id'         => $id
        ])->first();

        $newPhotos = '';

        $productPhotos = Product::getPhotos($id, true);
        //array_reverse($productPhotos);

        if (count($productPhotos) > 0) {
            foreach ($productPhotos as $index => $photo) {
                if ($index != $photoIndex) {
                    if ($index != (count($productPhotos) - 1) || $index != 0) {
                        $newPhotos .= $photo . ';';
                    } else {
                        $newPhotos .= $photo;
                    }
                } else {
                    if ($index == (count($productPhotos) - 1) && count($productPhotos) > 1) {
                        $newPhotos = substr($newPhotos, 0, (strlen($newPhotos) - 1));
                    }
                    $name = explode(':', $photo)[0];
                    Storage::delete('public/products/' . $name);
                    Storage::delete('public/products/thumbnail_' . $name);
                }
            }
        }
        $product->photos = $newPhotos;
        $product->save();

        User::addExperience($userId, -9);

        return json_encode(['message' => 'Photo of index ' . $photoIndex . ' from item ' . $id . ' removed!']);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function getAllProducts (Request $request)
    {
        $userId = $request->user()->id;

        $toReturn = array();

        $categories = Category::where('creator_id', $userId)->orderBy('name')->get();
        foreach ($categories as $cat) {

            $allProductsCount = 0;
            $emptyProductsCount = 0;

            // Get all products
            $productsNotEmpty = Product::where('category_id', $cat['id'])->where('remaining_amount', '>', '0')->orderby('brand_id')->get();
            $productsEmpty = Product::where('category_id', $cat['id'])->where('remaining_amount', '0')->orderby('brand_id')->get();

            $productsFormated = array();

            foreach ($productsNotEmpty as $product) {
                $brand = Brand::where('id', $product['brand_id'])->firstOrFail();
                array_push($productsFormated, [
                    'id'               => $product['id'],
                    'brand'            => $brand['name'],
                    'brand_id'         => $brand['id'],
                    'name'             => $product['name'],
                    'description'      => $product['description'],
                    'photos'           => $product['photos'],
                    'remaining_amount' => $product['remaining_amount'],
                    'uses_count'       => $product['uses_count'],
                    'rating'           => $product['rating'],
                    'pan'              => $product['pan'],
                    'empty'            => false,
                    'thumbnail'        => Product::getThumbnail($product['id'])
                ]);
                $allProductsCount++;
            }

            foreach ($productsEmpty as $product) {
                $brand = Brand::where('id', $product['brand_id'])->firstOrFail();
                array_push($productsFormated, [
                    'id'               => $product['id'],
                    'brand'            => $brand['name'],
                    'brand_id'         => $brand['id'],
                    'name'             => $product['name'],
                    'description'      => $product['description'],
                    'photos'           => $product['photos'],
                    'remaining_amount' => $product['remaining_amount'],
                    'uses_count'       => $product['uses_count'],
                    'rating'           => $product['rating'],
                    'pan'              => $product['pan'],
                    'empty'            => true,
                    'thumbnail'        => Product::getThumbnail($product['id'])
                ]);
                $emptyProductsCount++;
            }
            array_push($toReturn, [
                'category' => $cat,
                'products' => $productsFormated,
                'itemsNotEmpty' => $allProductsCount,
                'itemsEmpty' => $emptyProductsCount
            ]);
        }

        return json_encode($toReturn);
    }

}
