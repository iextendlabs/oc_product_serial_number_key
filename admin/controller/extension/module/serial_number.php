<?php
class ControllerExtensionModuleSerialNumber extends Controller
{
    private $error = array();

    public function install() {

        $query=$this->db->query("SHOW COLUMNS FROM `".DB_PREFIX."product` LIKE 'require_serial'");
        if(!$query->num_rows)
        $this->db->query("ALTER TABLE `".DB_PREFIX."product` ADD `require_serial` tinyint(1) NOT NULL");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."order_product_serial_numbers` (
          `product_serial_id` int(11) NOT NULL AUTO_INCREMENT,
          `order_product_id` int(11) NOT NULL,
          `serial` varchar(255) NOT NULL,
          `found` int(11) NOT NULL DEFAULT '0',
          `date-added` datetime NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`product_serial_id`)
        )");

        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."product_serial_numbers` (
          `product_serial_id` int(11) NOT NULL AUTO_INCREMENT,
          `product_id` int(11) NOT NULL,
          `serial` varchar(255) NOT NULL,
          PRIMARY KEY (`product_serial_id`)
        )");
    }
    private function write($message) {

        fwrite(fopen(DIR_LOGS . 'serials.log', 'a'), date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
    }

    public function index()
    {
        $this->install();
        $this->load->language('extension/module/serial_number');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $this->model_setting_setting->editSetting('module_serial_number', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }


        if (isset($this->error['products'])) {
            $data['error_products'] = $this->error['products'];
        } else {
            $data['error_products'] = '';
        }


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/serial_number', 'user_token=' . $this->session->data['user_token'], true)
        );


        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        
        if (isset($this->request->post['module_serial_number_user_group'])) {
            $data['module_serial_number_user_group'] = $this->request->post['module_serial_number_user_group'];
        } else {
            $data['module_serial_number_user_group'] = $this->config->get('module_serial_number_user_group');
        }

        if (isset($this->request->post['module_serial_number_status'])) {
            $data['module_serial_number_status'] = $this->request->post['module_serial_number_status'];
        } else {
            $data['module_serial_number_status'] = $this->config->get('module_serial_number_status');
        }

        if (isset($this->request->post['module_serial_number_order_status'])) {
            $data['module_serial_number_order_status'] = $this->request->post['module_serial_number_order_status'];
        } else {
            $data['module_serial_number_order_status'] = $this->config->get('module_serial_number_order_status');
        }

        if (isset($this->request->post['module_serial_number_order_product_auto_serial'])) {
            $data['module_serial_number_order_product_auto_serial'] = $this->request->post['module_serial_number_order_product_auto_serial'];
        } else {
            $data['module_serial_number_order_product_auto_serial'] = $this->config->get('module_serial_number_order_product_auto_serial');
        }

        if (isset($this->request->post['module_serial_number_product_serial_mode'])) {
            $data['module_serial_number_product_serial_mode'] = $this->request->post['module_serial_number_product_serial_mode'];
        } else {
            $data['module_serial_number_product_serial_mode'] = $this->config->get('module_serial_number_product_serial_mode');
        }


        if (isset($this->request->post['module_serial_number_mismatch_warning'])) {
            $data['module_serial_number_mismatch_warning'] = $this->request->post['module_serial_number_mismatch_warning'];
        } else {
            $data['module_serial_number_mismatch_warning'] = $this->config->get('module_serial_number_mismatch_warning');
        }

        $this->load->model('user/user_group');

        $data['user_groups'] = $this->model_user_user_group->getUserGroups();

        $data['action'] = $this->url->link('extension/module/serial_number', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/serial_number', $data));
    }

    public function findOrder()
    {

        $this->load->language('extension/module/serial_number');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );


        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('find_order_title'),
            'href' => $this->url->link('extension/module/serial_number/findOrder', 'user_token=' . $this->session->data['user_token'], true)
        );


        $data['action'] = $this->url->link('extension/module/serial_number/applySerialNumbers', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/serial_find_order', $data));
    }

    public function applySerialNumbers()
    {
        $this->load->language('extension/module/serial_number');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/serial_number');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );


        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('apply_serial_title'),
            'href' => $this->url->link('extension/module/serial_number/applySerialNumbers', 'user_token=' . $this->session->data['user_token'], true)
        );

        $order_id = '';

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['filter_order'])) {
            $order_id = $this->request->post['filter_order'];
        }

        if (isset($this->request->get['filter_order'])) {
            $order_id = $this->request->get['filter_order'];
        } 
            
        //get input data for product with serial
        if (isset($this->request->post['product_serial']) && $this->validate()) {

            $product_serials = $this->request->post['product_serial'];

            foreach ($product_serials as $order_product_id => $serials) {
                $product_name = $this->model_extension_serial_number->getOrderProductName($order_product_id);

                $product_name = substr($product_name, 0,150);
                // re-assign previously removed product serials to product 
                $this->model_extension_serial_number->restoreProductSerials($order_product_id, $order_id);

                $pro_serials = $this->model_extension_serial_number->getProductSerialsByOrderProduct($order_product_id);

                $string = array();
                // store product serials by order product into an array
                foreach ($pro_serials as $p_serial) {
                    foreach ($p_serial as $value) {
                        $string[] = $value;
                    }
                }

                $serials = explode("\n", $serials);

                $serials = array_filter(array_map('trim', $serials));

                $data['warning_serials'][$order_product_id] = array();

                foreach ($serials as $serial) {

                    if (!empty($pro_serials)) {
                        
                        if (in_array($serial, $string)) {

                            $this->model_extension_serial_number->addOrderProductSerials($order_product_id, $serial, $found = 1);

                            $this->model_extension_serial_number->deleteProductSerialsByOrder($serial);

                        } elseif (!in_array($serial, $string)) {

                            $data['warning'][$order_product_id] = true;
                            $this->model_extension_serial_number->addOrderProductSerials($order_product_id, $serial, $found = 0);

                            if (!in_array($serial, $data['warning_serials'][$order_product_id])) {
                                array_push($data['warning_serials'][$order_product_id], $serial);
                            }
                        }
                    } 
                    else {

                        $this->model_extension_serial_number->addOrderProductSerials($order_product_id, $serial, $found =   0);
                       
                    }

                    // product serial add log against order   
                    $this->write('Product('.$product_name.') serial  "'.$serial. '" is assigned to Order# '.$order_id );
                }
            }


            $data['success'] = 'Success: You have modified order product serial numbers';

        } else {
            if (isset($this->error['products'])) {
                $data['error_products'] = $this->error['products'];
            }
        }
        
        //get order products by order id
        if ((isset($this->request->post['filter_order']) || isset($this->request->get['filter_order']))) {


            if (isset($this->request->post['filter_order'])) {
                $order_id = $this->request->post['filter_order'];
            } else {
                $order_id = $this->request->get['filter_order'];
            }

            $data['order_id'] = $order_id;

            $data['products'] = $this->model_extension_serial_number->getOrderProducts($order_id);

            $products = $data['products'];

            foreach ($products as $key => $value) {

                $order_product_id = $products[$key]['order_product_id'];

                if (isset($this->request->post['product_serial']) && $this->validate() == false) {

                    $products[$key]['serial'] = $this->request->post['product_serial'][$order_product_id];

                } else {

                    $products[$key]['serial'] = $this->getProductSerials($order_product_id);

                }

            }
            $data['products'] = $products;

        }


        $data['show_warning'] = $this->config->get('module_serial_number_mismatch_warning');

        $data['action'] = $this->url->link('extension/module/serial_number/applySerialNumbers', 'filter_order=' . $order_id . '&user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/apply_serial_numbers', $data));
    }

    private function getProductSerials($product_id)
    {

        $serials_string = array();

        $product_serials = $this->model_extension_serial_number->getOrderProductSerials($product_id);

        if (!empty($product_serials)) {

            foreach ($product_serials as $product_serial) {

                array_push($serials_string, $product_serial['serial']);
            }

            return implode("\n", $serials_string);

        } else
            return '';
    }

    public function addProductSerials()
    {

        $this->load->language('extension/module/serial_number');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/serial_number', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('add_serial_title'),
            'href' => $this->url->link('extension/module/serial_number/addProductSerials', 'user_token=' . $this->session->data['user_token'], true)
        );

        $this->load->model('catalog/product');
        $this->load->model('extension/serial_number');


        //get product id
        if (isset($this->request->get['product_id'])) {

            $product_id = $this->request->get['product_id'];

        }
        else{

            $this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['p_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
        $data['p_serial'] = $this->model_extension_serial_number->getProductSerials($product_id);

            if ($data['p_serial']) {
                $serial = $data['p_serial'];

                foreach ($serial as $value) {
                    foreach ($value as $serial) {
                        $serials[] = $serial;
                    }
                }

                $serial = implode("\n", $serials);
                $data['product_serial'] = $serial;
            } else {
                $data['product_serial'] = '';
            }


        if (isset($this->request->post['product_serial']) && $this->validateAddProductSerials()) {

            if ($data['p_serial']) {
                $this->model_extension_serial_number->deleteProductSerials($product_id);
            }

            $product_serial = $this->request->post['product_serial'];

            $product_serials = explode("\n", $product_serial);

            $product_serials = array_filter(array_map('trim', $product_serials));

            foreach ($product_serials as $serial) {

                $data['product_serials'] = $this->model_extension_serial_number->addProductSerials($product_id, trim($serial));

            }

            $this->session->data['success'] = $this->language->get('success');

            $this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true));

        }



        if (isset($data['p_description'][(int)$this->config->get('config_language_id')]))
            $data['p_description'] = array($data['p_description'][(int)$this->config->get('config_language_id')]);

        $data['user_token'] = $this->session->data['user_token'];

        $data['action'] = $this->url->link('extension/module/serial_number/addProductSerials&product_id=' . $product_id, 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/product_serials', $data));
    }

    public function findOrderBySerial()
    {
        $this->load->language('extension/module/serial_number');
        $this->load->language('sale/order');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );


        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('find_order_by_serial_title'),
            'href' => $this->url->link('extension/module/serial_number/findOrderBySerial', 'user_token=' . $this->session->data['user_token'], true)
        );

        // API login
        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
        
        // API login
        $this->load->model('user/api');

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
            $session = new Session($this->config->get('session_engine'), $this->registry);

            $session->start();

            $this->model_user_api->deleteApiSessionBySessionId($session->getId());

            $this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

            $session->data['api_id'] = $api_info['api_id'];

            $data['api_token'] = $session->getId();
        } else {
            $data['api_token'] = '';
        }



        if (isset($this->request->post['filter_order_serial'])) {
            $this->load->model('extension/serial_number');

            $data['filter_order_serial'] = $this->request->post['filter_order_serial'];
            $data['orders'] = $this->model_extension_serial_number->getOrdersByProductSerial($data['filter_order_serial']);

            if ($data['orders']) {
                $data['order_id'] = $data['orders'][0]['order_id'];

                 $data['add_serial_url'] = $this->url->link('extension/module/serial_number/applySerialNumbers&order_id=' . $data['order_id'], 'user_token=' . $this->session->data['user_token'], true);

                $data['order_view_url'] = $this->url->link('sale/order/info&order_id=' . $data['order_id'], 'user_token=' . $this->session->data['user_token'], true);

                $data['order_edit_url'] = $this->url->link('sale/order/edit&order_id=' . $data['order_id'], 'user_token=' . $this->session->data['user_token'], true);
            } else {
                $data['no_result'] = 'No result found';
            }

        } else {
            $data['filter_order_serial'] = '';
        }

         $data['restricted_view'] = false;
        
        $user_group_id = $this->user->getGroupId();

        if($user_group_id == $this->config->get('module_serial_number_user_group')){
            $this->request->get['filter_order_status_id'] = $this->config->get('module_serial_number_order_status');
            $data['restricted_view'] = true;
        }

        $data['action'] = $this->url->link('extension/module/serial_number/findOrderBySerial', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/order_by_serial', $data));
    }

    private function checkProductSerials()
    {
        $error = array();
        if (isset($this->request->post['product_serial'])) {
            $product_serials = $this->request->post['product_serial'];

            foreach ($product_serials as $key => $serial) {
                $serials = explode("\n", $serial);
                $serials = array_filter(array_map('trim', $serials));
                $serials_count = count($serials);

                $product_quantity = $this->request->post['product_quantity'][$key];

               
                if ($serials_count != 0 && $serials_count != $product_quantity)
                    $error[$key] = true;

            }
        }
        return $error;
    }


    protected function validate()
    {

        if (!$this->user->hasPermission('modify', 'extension/module/serial_number')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $errors = $this->checkProductSerials();

        if (!empty($errors)) {
            $this->error['products'] = $errors;
        }

        return !$this->error;
    }

    protected function validateAddProductSerials()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/serial_number')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}