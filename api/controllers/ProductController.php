<?php

if($_SERVER['HTTP_HOST'] == DB_HOST_1) {
    include 'api/models/Product.php';
} else {
    include 'models/Product.php';
}

class ProductController {
    private $product;
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {
        $this->product = new Product();
    }
    
    /**
     * get
     * get all products from the product table
     * @param  array $queryParams
     * @return void
     */
    public function get($queryParams) {
        try {
            $products = $this->product->getAllProducts($queryParams);
            http_response_code(200);
            echo json_encode(['products' => $products]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * getByCode
     * get a product and it's prerequisites and credit given product code from the product table
     * @param  string $code
     * @return void
     */
    public function getByCode($code, $queryParams) {
        try {
            $product = $this->product->getProductByCode($code, $queryParams);
            http_response_code(200);
            echo json_encode(['product' => $product]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * create
     * creates a new product and adds it to the product table
     * @return void
     */
    public function create() {
        try {
            $productData = REQ_BODY();
            
            if (empty($productData) || empty($productData['code']) || empty($productData['credits']) || empty($productData['title']) || empty($productData['offered'])) {
                throw new Exception(handleError(MISSING_PARAMS_ERROR, 400));
            }
        
            $newproduct = $this->product->createproduct($productData);
            http_response_code(201); // Created
            echo json_encode(['product' => $newproduct]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * update - Updates an entry in the database given its id
     * @param  string $id - the product code provided that will be updated in the db
     * @return void
     */
    public function update($id) {
        try {
            $productData = REQ_BODY();
            
            if (empty($productData) || empty($id)) {
                throw new Exception(handleError(MISSING_PARAMS_ERROR, 400));
            }

            $updatedproduct = $this->product->updateproduct($id, $productData);
            http_response_code(200); // Updated
            echo json_encode(['product' => $updatedproduct]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        
    }

    /**
     * delete
     * Deletes an entry from products table given the id
     * @param  string $id - given product code to delete from db
     * @return void
     */
    public function delete($id) {
        try {
            if (empty($id)) {
                throw new Exception(handleError(MISSING_PARAMS_ERROR, 400));
            }

            $this->product->deleteproduct($id);
            http_response_code(200);
            echo json_encode(['productCode' => $id]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
?>
