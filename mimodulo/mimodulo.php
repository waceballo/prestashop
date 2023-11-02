<?php
if(!defined('_PS_VERSION_'))
exit;

class MiModulo extends Module
{
    public function __construct()
    {
        $this->name = 'hinetapi';
        $this->tab = 'checkout';
        $this->version = '1.0.0';
        $this->author = 'HINET';
        $this->need_instance = 0;
        
        parent::__construct();
        
        $this->displayName = $this->l('Módulo HINET API ');
        $this->description = $this->l('Descripción del Módulo para enviar ordenes al ERP.');
        
        // Registra los hooks que deseas utilizar
        $this->registerHook('actionValidateOrder');
    }

    // Implementa la función hookActionValidateOrder para capturar eventos de órdenes

	public function hookActionValidateOrder($params){
	    $order = $params['order'];

	    // Captura y formatea los datos de la orden según las necesidades de la API REST
	    // ... datos de la orden ...
	    $data['order']['order_id'] = $order->id_order;
	    $data['order']['shipment_type'] = '';
	    $data['order']['seller_id'] = 0;
	    $data['order']['empresa'] = '01';
	    $data['order']['deposito'] = '01';
	    $data['order']['punto'] = '00001';

		// Obtener el ID del cliente asociado a la orden
		$customer_id = $order->id_customer;

		// Cargar el objeto del cliente
		$customer = new Customer($customer_id);

		// Acceder a los datos del cliente
	    $data['order']['doc_type'] = $customer->dni;
	    $data['order']['DNI'] = $customer->vat_number;
	    $data['order']['ApellidoyNombre'] = $customer->firstname . ' ' . $customer->lastname;
	    $data['order']['Direccion'] = $customer->address1;
	    $data['order']['Ciudad'] = $customer->city;
	    $data['order']['Provincia'] = $customer->state;
	    $data['order']['Email'] = $customer->email;
	    $data['order']['Telefono'] = $customer->phone;

	    $data['order']['FechaPago'] = $order->date_add;
	    $data['order']['NroMercadoPago1'] = '';
	    $data['order']['ImportePago1'] = $order->total_paid;
	    $data['order']['NroMercadoPago2'] = '';
	    $data['order']['ImportePago2'] = 0;
	    $data['order']['NroMercadoPago3'] = '';
	    $data['order']['ImportePago3'] = 0;
	    $data['order']['NroMercadoPago4'] = '';
	    $data['order']['ImportePago4'] = 0;
	    $data['order']['NroMercadoPago5'] = ;
	    $data['order']['ImportePago5'] = 0;
	    $data['order']['NroMercadoPago6'] = '';

	    $data['order']['retiraEnSucursal'] = '';

		// Get the order details (items)
		$orderDetails = $order->getOrderDetailList();

		// Loop through the order details to access product information
		$items = array();
		foreach ($orderDetails as $orderDetail) {
		    $product_id = $orderDetail['product_id'];
		    $product_name = $orderDetail['product_name'];
		    $product_quantity = $orderDetail['product_quantity'];
		    $product_price = $orderDetail['unit_price_tax_incl'];
		    
		    $items[]['Sku'] = $product_id;
		    $items[]['Cantidad'] = $product_quantity;
		    $items[]['Descripcion'] = $product_name;
		    $items[]['Precio'] = (float)$product_price*$product_quantity;
		}
	    $data['order']['itemDetails'] = $items;

	    // Convierte los datos en formato JSON
	    $json_data = json_encode($data);

	    // Realiza la solicitud a la API REST
	    $api_url = 'https://hinet24.net.ar/hn24/api/v1/AgregarPedidoMultipagosHn24';
	    $response = $this->sendRequestToApi($api_url, $json_data);


	    // Maneja la respuesta de la API (parsing, registro, etc.)
	}

	private function sendRequestToApi($url, $data){
	    // Implementa la lógica para enviar la solicitud HTTP aquí (puedes usar cURL, file_get_contents, etc.)

	    // Ejemplo con cURL:
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $response = curl_exec($ch);
	    curl_close($ch);

	    return $response;
	}



}
