<?php
 if (!defined('_PS_VERSION_'))
 exit;
 class Price_Product extends Module
 {
	 public function __construct()
	 {
		 $this->name = 'price_product'; 
		 $this->tab = 'front_office_features'; 
		 $this->version = '1.0'; 
		 $this->author = 'Randel'; 
		 $this->need_instance = 0; 
		 $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		 $this->bootstrap = true;
		 parent::__construct();
		 $this->displayName = $this->l('Товары ОТ-ДО ');
		 $this->description = $this->l('Выводит сколько товаров в магазине находятся в указанном ценовом диапазоне');
		 $this->confirmUninstall = $this->l('Вы действительно хотите удалить модуль?');
		 if (!Configuration::get('price_product'))
		 $this->warning = $this->l('Извините, произошла ошибка!'); 
		}
	 public function install()
	 {
	 	Configuration::updateValue('price_product_from', null);
        Configuration::updateValue('price_product_to', null);
		return parent::install() && $this->registerHook('DisplayFooter');
	 }
	 //удаление модуля
	 public function uninstall()
	 {
	 	Configuration::deleteByName('price_product_from');
        Configuration::deleteByName('price_product_to');

	 	return parent::uninstall();
	 }

	 public function getContent()
     {
        /**
         * If values have been submitted in the form, process.
         */
         if (((bool)Tools::isSubmit('submitprice_product')) == true) {
            $this->postProcess();
         }

         $this->context->smarty->assign('module_dir', $this->_path);

         /*$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');*/

         return $output.$this->renderForm();
     }
     protected function renderForm()
     {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitprice_product';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), 
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
     }
     protected function getConfigForm()
     {
         return array(
             'form' => array(
                 'legend' => array(
                 'title' => $this->l('Settings'),
                 'icon' => 'icon-cogs',
                 ),
                 'input' => array(
                     array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '',
                        'desc' => $this->l('For example: 10'),
                        'name' => 'price_product_from',
                        'label' => $this->l('Price from'),
                     ),
                     array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '',
                        'desc' => $this->l('For example: 100'),
                        'name' => 'price_product_to',
                        'label' => $this->l('Price to'),
                     ),
                 ),
                 'submit' => array(
                    'title' => $this->l('Save'),
                 ),
             ),
         );
     }
     protected function getConfigFormValues()
    {
        return array(
            'price_product_from' => Configuration::get('price_product_from', null),
            'price_product_to' => Configuration::get('price_product_to', null),
        );
    }
     protected function postProcess()
     {
        $form_values = $this->getConfigFormValues();

         foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
         }
         
        
     }
	 public function hookDisplayFooter()
    {
      $values1 = Configuration::get('price_product_from');
      $int1 = (int)$values1;
      $values2 = Configuration::get('price_product_to');
      $int2 = (int)$values2;	
      $sql = 'SELECT COUNT(*) FROM `'._DB_PREFIX_.'product` WHERE price >= '.$int1.' and price <= '.$int2.'';
      $totalShop = Db::getInstance()->getValue($sql);
      // Назначаем переменные для шаблона smarty
      $this->context->smarty->assign([
          'price_product_from' => Configuration::get('price_product_from'),
          'price_product_to' => Configuration::get('price_product_to'),
          // Добавим из таблицы БД ps_configuration телефон:
          'count_price_product' => $totalShop
        ]);

        return $this->display(__FILE__, 'price_product.tpl');
    }

 }