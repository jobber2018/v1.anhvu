<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Product\Service;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Product\Entity\Product;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Hotels\Service\HotelManage;
use Product\Entity\ProductCat;
use Product\Entity\ProductRecommend;
use Product\Entity\ProductUnit;
use Sulde\Service\Common\SessionManager;

class ProductManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Product
     */
    public function getById($p_id){
        $product = $this->entityManager->getRepository(Product::class)->find($p_id);
        return $product;
    }

    /**
     * @param $p_id
     * @return ProductCat
     */
    public function getCatById($p_id){
        $productCat = $this->entityManager->getRepository(ProductCat::class)->find($p_id);
        return $productCat;
    }

    /**
     * @param $p_id
     * @return ProductUnit
     */
    public function getUnitById($p_id){
        $productUnit = $this->entityManager->getRepository(ProductUnit::class)->find($p_id);
        return $productUnit;
    }

    /**
     * @return Product
     */
    public function getAll()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.is_del=0')
            ->andWhere('p.active=1')
            ->orderBy('p.sort', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Product
     */
    public function getDeleted()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.is_del=1')
            ->orderBy('p.sort', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Product
     */
    public function getInactive()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->andWhere('p.active=0')
            ->orderBy('p.sort', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return ProductCat
     */
    public function getProductCatList()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('pc')
            ->from(ProductCat::class, 'pc')
            ->andWhere('pc.active=1')
            ->orderBy('pc.sort', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    public function getUnitList()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('pu')
            ->from(ProductUnit::class, 'pu')
            ->orderBy('pu.name', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_k
     * @return Product
     */
    public function search($p_k)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.id > 1');

        if($p_k) {
            $queryBuilder->andWhere('p.name LIKE :name')
                ->setParameter('name', $p_k.'%');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function searchPaginator($p_keyword, $p_length, $p_start)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.active = 1')
            ->andWhere('p.is_del=0')
            ->setFirstResult($p_start)
            ->setMaxResults($p_length)
            ->orderBy('p.name', 'ASC');

        if($p_keyword) {
            $queryBuilder->andWhere('p.name LIKE :name OR p.code LIKE :barcode')
                ->setParameter('name', '%'.$p_keyword.'%')
                ->setParameter('barcode', '%'.$p_keyword.'%');
        }
        return new Paginator($queryBuilder->getQuery());
    }

    /**
     * @param $p_keyword
     * @param $p_length
     * @param $p_start
     * @param $p_columnOrder
     * @param $p_orderDir
     * @return Paginator
     */
    public function productSearch($p_keyword, $p_length, $p_start,$p_columnOrder,$p_orderDir){
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.active = 1')
            ->andWhere('p.is_del=0')
            ->setFirstResult($p_start)
            ->setMaxResults($p_length);

        if($p_columnOrder)
            $queryBuilder->orderBy('p.'.$p_columnOrder, $p_orderDir);
        else
            $queryBuilder->orderBy('p.id', 'DESC');

        if($p_keyword) {
            $queryBuilder->andWhere('p.name LIKE :name OR p.code LIKE :barcode OR p.pack_code LIKE :barcode OR p.code_1 LIKE :barcode OR p.code_2 LIKE :barcode OR p.code_3 LIKE :barcode')
                ->setParameter('name', '%'.$p_keyword.'%')
                ->setParameter('barcode', '%'.$p_keyword.'%');
        }
        return new Paginator($queryBuilder->getQuery());
    }

    /**
     * @return Product
     */
    public function getAllForPrice()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.active = 1')
            ->andWhere('p.is_del=0')
            ->orderBy('p.sort', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Product
     */
    public function getProductSortTable()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.active = 1')
            ->andWhere('p.is_del=0')
            ->orderBy('p.sort_price_table', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    public function getProductNorm()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.norm > p.inventory')
            ->andWhere('p.active = 1')
            ->andWhere('p.is_del=0')
            ->orderBy('p.inventory', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param array $p_products
     * @return Product
     */
    public function getMyCard(array $p_products)
    {
        $configuration = $this->entityManager->getConfiguration();
//        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where("p.id IN (:products)")
            ->setParameter('products', $p_products);
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_catId
     * @return Product
     */
    public function getProductCatById($p_catId)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.productCat = :catId')
            ->andWhere('p.active = 1')
            ->andWhere('p.is_del=0')
            ->setParameter('catId', $p_catId)
            ->setFirstResult(0)
            ->setMaxResults(12)
            ->orderBy('p.inventory', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return ProductRecommend
     */
    public function getRecommend()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('pr')
            ->from(ProductRecommend::class, 'pr')
            ->orderBy('pr.sort', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $productId
     * @return ProductRecommend
     */
    public function getRecommendByProductId($productId)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('pr')
            ->from(ProductRecommend::class, 'pr')
            ->where('pr.product = :product')
            ->setParameter('product', $productId);

        return $queryBuilder->getQuery()->getSingleResult();
    }

    /**
     * @param $p_code
     * @return Product
     */
    public function getBarcode($p_code)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->where('p.code = :code')
            ->orWhere('p.pack_code = :code')
            ->orWhere('p.code_1 = :code')
            ->orWhere('p.code_2 = :code')
            ->orWhere('p.code_3 = :code')
            ->setParameter('code', $p_code);
        return $queryBuilder->getQuery()->getResult();
    }

}