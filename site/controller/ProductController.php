<?php
class ProductController
{
    // Hiển thị danh sách sản phẩm
    function index($category_id=null, $priceRange = null)
    {
        $categoryRepository = new CategoryRepository();
        $categories = $categoryRepository->getAll();

        $conds = [];
        $sorts = [];
        $page = $_GET['page'] ?? 1;
        $item_per_page = 10;
        $categoryName = 'Tất cả sản phẩm';
        if ($category_id) {
            $conds = [
                'category_id' => [
                    'type' => '=',
                    'val' => $category_id
                ]
            ];
            // SELECT * from view_product WHERE category_id=3

            $category = $categoryRepository->find($category_id);
            $categoryName = $category->getName();
        }

        // Tìm kiếm theo giá
        if ($priceRange) {
            $temp = explode('-', $priceRange);
            $start = $temp[0];
            $end = $temp[1];
            $conds = [
                'sale_price' => [
                    'type' => 'BETWEEN',
                    'val' => "$start AND $end"
                ]
            ];
            // SELECT * from view_product WHERE sale_price BETWEEN 100000 AND 200000
            // giá lớn hơn 1000000 (1 triệu)
            if ($end == 'greater') {
                $conds = [
                    'sale_price' => [
                        'type' => '>=',
                        'val' => $start
                    ]
                ];
                // SELECT * from view_product WHERE sale_price >= 1000000
            }
        }

        // Tìm kiếm theo từ khóa
        $search = $_GET['search'] ?? null;
        if ($search) {
            $conds = [
                'name' => [
                    'type' => 'LIKE',
                    'val' => "'%$search%'"
                ]
            ];
        }
        // SELECT * from view_product WHERE name LIKE '%kem%'

        // Sắp xếp
        // sort=price-desc
        $sort = $_GET['sort'] ?? null;
        if ($sort) {
            $temp = explode('-', $sort);
            $col = $temp[0];
            $order = strtoupper($temp[1]); //desc => DESC
            $map = ['price' => 'sale_price', 'alpha' => 'name', 'created' => 'created_date'];
            $colName = $map[$col]; //price -> sale_price
            $sorts = [
                $colName => $order
            ];
            // SELECT * FROM view_product ORDER BY sale_price DESC
        }
        $productRepository = new ProductRepository();
        $products = $productRepository->getBy($conds, $sorts, $page, $item_per_page);

        // Tìm tổng số trang
        $allProducts = $productRepository->getBy($conds, $sorts);
        $totalPage = ceil(count($allProducts) / $item_per_page);
        require ABSPATH_SITE . 'view/product/index.php';
    }

    function detail($id)
    {
        $categoryRepository = new CategoryRepository();
        $categories = $categoryRepository->getAll();
        
        $productRepository = new ProductRepository();
        $product = $productRepository->find($id);
        $category_id =$product->getCategoryId();
        // sản phẩm có liên quan
        $conds = [
            'category_id' => [
                'type' => '=',
                'val' => $category_id
            ],
            'id' => [
                'type' => '!=',
                'val' => $product->getId()
            ]
        ];
        // SELECT * FROM view_product WHERE category_id = 3 AND id != 5
        $relatedProducts = $productRepository->getBy($conds, [], 1, 10);

        require ABSPATH_SITE . 'view/product/detail.php';
    }

    function storeComment()
    {
        $data["product_id"]  = $_POST['product_id'];
        $data["description"] = $_POST['description'];
        $data["created_date"] = date('Y-m-d H:i:s');
        $data["star"] = $_POST['rating'];
        $data["fullname"] = $_POST['fullname'];
        $data["email"] = $_POST['email'];
        $commentRepository = new CommentRepository();
        $commentRepository->save($data);
    }
}