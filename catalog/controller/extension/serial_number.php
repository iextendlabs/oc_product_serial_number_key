<?php
class ControllerExtensionSerialNumber extends Controller
{
    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/order', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $customer_id = $this->customer->isLogged();

        $this->load->language('account/order');

        $this->document->setTitle($this->language->get('heading_title'));
        
        $url = '';

    
        
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/order', $url, true)
        );

         $data['breadcrumbs'][] = array(
            'text' => $this->language->get('find_order'),
            'href' => $this->url->link('extension/serial_number', $url, true)
        );


        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['orders'] = array();



        //find order by serial
        if (isset($this->request->post['filter_order_serial'])) {
            $this->load->model('extension/serial_number');
            $data['filter_order_serial'] = trim($this->request->post['filter_order_serial']);

            if (!empty($data['filter_order_serial'])) {
                $data['orders'] = $this->model_extension_serial_number->getOrdersByProductSerial($data['filter_order_serial'], $customer_id);

                if ($data['orders']) {
                    $data['order_total_products'] = $this->model_extension_serial_number->getTotalOrderProductsByOrderId($data['orders'][0]['order_id']);

                    $data['orders'][0]['total_products'] = $data['order_total_products'];
                    $data['orders'][0]['total'] = $this->currency->format($data['orders'][0]['total'], $data['orders'][0]['currency_code'], $data['orders'][0]['currency_value']);
                } else {
                    $data['no_result'] = 'No record found ';
                }
            }
        } else {
            $data['filter_order_serial'] = '';
        }

        $data['action'] = $this->url->link('extension/serial_number');
        $data['view'] = $this->url->link('account/order/info');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/orders_by_serial_number', $data));
    }
}
