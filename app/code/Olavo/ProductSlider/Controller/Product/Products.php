<?php

namespace Olavo\ProductSlider\Controller\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductRepository;

class Products extends \Magento\Framework\App\Action\Action
{
    protected $categoryRepository;
    protected $productCollectionFactory;
    protected $resultJsonFactory;
    protected $categoryFactory;
    protected $productFactory;
    protected $productRepository;
    protected $_imageHelper;

    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $productCollectionFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        ProductRepository $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->_imageHelper = $imageHelper;
    }

    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id');

        // Carregar os produtos da categoria
        $products = [];
        $productCollection = $this->getProductCollection($categoryId);
        $productData = $productCollection->getData();

        // Iterar sobre os dados dos produtos
        foreach ($productData as $product) {

            // Criar uma instância de produto a partir dos dados
            $productObject = $this->productFactory->create()->load($product['entity_id']);

            // Obtenha o preço base
            $price = $this->getBasePrice($productObject);

            // Preço dividido por 6
            $price_6 = $price / 6;

            // Obtenha o preço especial, se houver
            $specialPrice = null;
            if ($productObject->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                // Se for um produto configurável, obtenha as variações associadas
                $configurableProduct = $this->productRepository->getById($productObject->getId());
                $simpleProducts = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);

                // Itere sobre as variações para encontrar o menor preço especial
                foreach ($simpleProducts as $simpleProduct) {
                    $variationSpecialPrice = $simpleProduct->getSpecialPrice();
                    if ($variationSpecialPrice !== null && ($specialPrice === null || $variationSpecialPrice < $specialPrice)) {
                        $specialPrice = $variationSpecialPrice;
                    }
                }
            } else {
                // Se não for um produto configurável, obtenha o preço especial diretamente do produto
                $specialPrice = $productObject->getSpecialPrice();
            }


            // Obter o valor do atributo swatch de cores, se disponível
            $swatchValue = $this->getSwatchValue($productObject);

            // Adicione o produto à lista
            $products[] = [
                'id' => $product['entity_id'],
                'name' => $productObject->getName(),
                'price' => $price,
                'price6x' => $price_6,
                'special_price' => $specialPrice,
                'image' => $this->getProductImage($productObject),
                'swatch_color' => $swatchValue // Adicione o valor do atributo swatch de cores
                // Adicione mais atributos conforme necessário
            ];
        }

        // Retornar a resposta JSON
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($products);
    }

    protected function getSwatchValue($product)
    {
        // Verifique se o produto tem um atributo com swatch de cores
        $swatchAttributeCode = 'color'; // Altere para o código do seu atributo de swatch de cores
        if ($product->getData($swatchAttributeCode)) {
            return $product->getData($swatchAttributeCode);
        }
        return null;
    }


    protected function getProductCollection($categoryId)
    {
        return $this->categoryFactory->create()->load($categoryId)->getProductCollection()->addAttributeToSelect('*');
    }

    protected function getBasePrice($product)
    {
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            // Se for um produto configurável, carregue as variações associadas
            $configurableProduct = $this->productRepository->getById($product->getId());
            $simpleProducts = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);

            // Inicializar uma lista de preços das variações
            $prices = [];

            // Coletar os preços das variações
            foreach ($simpleProducts as $simpleProduct) {
                $prices[] = $simpleProduct->getPrice();
            }

            // Calcular e retornar o preço base (média dos preços das variações)
            return count($prices) > 0 ? array_sum($prices) / count($prices) : 0;
        } else {
            // Se não for um produto configurável, retorne o preço normal
            return $product->getFinalPrice();
        }
    }

    protected function getProductImage($product)
    {
        // Retorna a imagem principal do produto configurável
        return $this->_imageHelper->init($product, 'product_base_image')->getUrl();
    }
}
