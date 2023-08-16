<?php
class HomeController
{
    // Hiển thị trang chủ
    function index()
    {
        // Lấy 4 sản phẩm nổi bật
        // sắp xếp cột featured giảm dần, sau đó lấy 4
        $page = 1;
        $item_per_page = 4;
        $conds = [];
        $sorts = ['featured' => 'DESC'];
        $productRepository = new ProductRepository();
        $featuredProducts = $productRepository->getBy($conds, $sorts, $page, $item_per_page);
        // SELECT * FROM view_product ORDER BY featured DESC LIMIT 0, 4

        // Lấy 4 sản phẩm mới nhất
        $sorts = ['created_date' => 'DESC'];
        $productRepository = new ProductRepository();
        $latestProducts = $productRepository->getBy($conds, $sorts, $page, $item_per_page);
        // SELECT * FROM view_product ORDER BY created_date DESC LIMIT 0, 4

        // Lấy 4 sản phẩm theo từng danh mục
        $categoryRepository = new CategoryRepository();
        $categories = $categoryRepository->getAll();

        $categoryProducts = [];
        // Duyệt từng danh mục để lấy tên danh mục và sản phẩm tương ứng
        foreach ($categories as $category) {
            $conds = [
                'category_id' => [
                    'type' => '=',
                    'val' => $category->getId()
                ]
            ];

            $products = $productRepository->getBy($conds, $sorts, $page, $item_per_page);
            // SELECT * from view_product WHERE category_id=2;

            // Thêm sản phẩm vào array
            $categoryProducts[] = [
                'categoryName' => $category->getName(),
                'products' => $products
            ];
        }
        require ABSPATH_SITE .'view/home/index.php';
    }
}