<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class ProductCategories extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'productcategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Piotr Chmielowiec';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max'   => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Products from selected category', array(), 'Modules.Productcategories.Admin');
        $this->description = $this->trans('Displays products in the central column of your homepage.', array(), 'Modules.Productcategories.Admin');

        $this->templateFile = 'module:productcategories/views/templates/hook/categories_products.tpl';
    }

    public function install()
    {
        $this->_clearCache('*');

        Configuration::updateValue('FIRST_CAT_ID', '2');
        Configuration::updateValue('SECOND_CAT_ID', '3');

        return (parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('actionFrontControllerSetMedia')
        );
    }

    public function uninstall()
    {
        $this->_clearCache('*');

        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        $errors = array();

        if (Tools::isSubmit('submitBlockCategories')) {
            $first_cat_id = (int) (Tools::getValue('FIRST_CAT_ID'));
            if (!Validate::isInt($first_cat_id) || $first_cat_id <= 0) {
                $errors[] = $this->trans('The category ID is invalid. Please choose an existing category ID.', array(), 'Modules.Productcategories.Admin');
            }

            $second_cat_id = (int) (Tools::getValue('SECOND_CAT_ID'));
            if (!Validate::isInt($second_cat_id) || $second_cat_id <= 0) {
                $errors[] = $this->trans('The category ID is invalid. Please choose an existing category ID.', array(), 'Modules.Productcategories.Admin');
            }

            if (isset($errors) && count($errors)) {
                $output = $this->displayError(implode('<br />', $errors));
            } else {
                Configuration::updateValue('FIRST_CAT_ID', (int) $first_cat_id);
                Configuration::updateValue('SECOND_CAT_ID', (int) $second_cat_id);

                $this->_clearCache('*');

                $output = $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
            }
        }

        return $output . $this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->getTranslator()->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs',
                ],
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('First category ID', array(), 'Modules.Productcategories.Admin'),
                        'name' => 'FIRST_CAT_ID'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Second category ID', array(), 'Modules.Productcategories.Admin'),
                        'name' => 'SECOND_CAT_ID'
                    )
                ),
                'submit' => array(
                    'title' => $this->getTranslator()->trans('Save', [], 'Admin.Actions'),
                ),
            ],
        ];

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBlockCategories';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'FIRST_CAT_ID' => Tools::getValue('FIRST_CAT_ID', (int) Configuration::get('FIRST_CAT_ID')),
            'SECOND_CAT_ID' => Tools::getValue('SECOND_CAT_ID', (int) Configuration::get('SECOND_CAT_ID')),
        );
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('productcategories'))) {
            $variables = $this->getWidgetVariables($hookName, $configuration);

            if (empty($variables)) {
                return false;
            }

            $this->smarty->assign($variables);
        }

        return $this->fetch($this->templateFile, $this->getCacheId('productcategories'));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $first_cat_id = $this->getConfigFieldsValues()['FIRST_CAT_ID'];
        $first_cat_products = $this->getProducts( $first_cat_id );
        $second_cat_id = $this->getConfigFieldsValues()['SECOND_CAT_ID'];
        $second_cat_products = $this->getProducts( $second_cat_id );
        $first_category = new Category ($first_cat_id,Context::getContext()->language->id);
        $second_category = new Category ($second_cat_id,Context::getContext()->language->id);

        $variables = array();

        if (!empty($first_cat_id)) {
            $variables['categories']['fitstCategory'] = array(
                'products' => $first_cat_products,
                'categoryLink' => Context::getContext()->link->getCategoryLink($this->getConfigFieldsValues()['FIRST_CAT_ID']),
                'categoryName' => $first_category->name
            );
        }

        if (!empty($second_cat_products)) {
            $variables['categories']['secondCategory'] = array(
                'products' => $second_cat_products,
                'categoryLink' => Context::getContext()->link->getCategoryLink($this->getConfigFieldsValues()['SECOND_CAT_ID']),
                'categoryName' => $second_category->name
            );
        }

        if (!empty($variables)) {
            return $variables;
        }

        return false;
    }

    private function getProducts( $cat_id )
    {
        $id_category = $cat_id;
        $context = Context::getContext();
        $order_by = 'position';
        $order_way = 'ASC';
        $lang = $this->context->language;
        $id_lang = $lang->id;
        $only_active = true;
        $limit = 10;

        $front = true;
        if (!in_array($context->controller->controller_type, ['front', 'modulefront'])) {
            $front = false;
        }

        $sql = new DbQuery();
        $sql->select('p.*, product_shop.*, pl.*, m.name AS manufacturer_name, s.name AS supplier_name,sav.quantity  AS sav_quantity');
        $sql->from('product', 'p' . Shop::addSqlAssociation('product', 'p'));
        $sql->leftJoin('product_lang', 'pl',  'p.id_product = pl.id_product ' . Shop::addSqlRestrictionOnLang('pl'));
        $sql->leftJoin('stock_available', 'sav',  'sav.id_product = p.id_product AND sav.id_product_attribute = 0 AND sav.id_shop = 1  AND sav.id_shop_group = 0');
        $sql->leftJoin('manufacturer', 'm', 'm.id_manufacturer = p.id_manufacturer');
        $sql->leftJoin('supplier', 's', 's.id_supplier = p.id_supplier');
        $id_category ? $sql->leftJoin('category_product', 'c', 'c.id_product = p.id_product') : '';
        $sql->where('pl.id_lang = ' . (int) $id_lang . ($id_category ? ' AND c.id_category = ' . (int) $id_category : '') . ($front ? ' AND product_shop.visibility IN ("both", "catalog")' : '') . ($only_active ? ' AND product_shop.active = 1' : '') . ' AND sav.quantity  >=1');
        $sql->orderBy(pSQL($order_by));
        $sql->limit((int)$limit, 0);

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        foreach ($rq as &$row) {
            $row = Product::getTaxesInformations($row);
        }

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = $presenterFactory->getPresenter();

        $products_for_template = [];

        foreach ($rq as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $products_for_template;
    }

    public function hookActionFrontControllerSetMedia() {
        $this->context->controller->registerStylesheet(
            'owl-carousel-min',
            'modules/' . $this->name . '/lib/css/owl.carousel.min.css',
            [
                'media' => 'all',
                'priority' => 100,
            ]
        );

        $this->context->controller->registerStylesheet(
            'owl-carousel-theme-default-min',
            'modules/' . $this->name . '/lib/css/owl.theme.default.min.css',
            [
                'media' => 'all',
                'priority' => 100,
            ]
        );

        $this->context->controller->registerStylesheet(
            'categories_products-style',
            'modules/' . $this->name . '/views/css/categories_products.css',
            [
                'media' => 'all',
                'priority' => 100,
            ]
        );

        $this->context->controller->registerJavascript(
            'owl-carousel-js',
            'modules/' . $this->name . '/lib/js/owl.carousel.min.js',
            [
              'inline' => false,
              'priority' => 100,
            ]
        );

        $this->context->controller->registerJavascript(
            'categories_products-js',
            'modules/' . $this->name . '/views/js/categories_products.js',
            [
              'inline' => false,
              'priority' => 100,
            ]
        );
    }
}
