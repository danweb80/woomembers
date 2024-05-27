<?php

/*
 * Plugin Name: WooMembers
 * Description: Cadastra automaticamente um cliente que acabou de comprar seu Produto pelo Woocommerce na plataforma The Members.
 * Author: Daniel Weber 
 * Author URI: mailto://prof.daniel.weber@gmail.com
 * Version: 1.0
 * License: GPLv2 or later
 */

 //Prefix/slug - woomembers

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//******************************************************************************
//******************************************************************************
//				 PÁGINA DE CONFIGURAÇÕES DO WOOCOMMERCE
//******************************************************************************
//******************************************************************************

// ALGUNS LINKS QUE ME AJUDARAM A CONSTRUIR A PÁGINA DE CONFIGURAÇÕES
//Principalmente>>>> https://wordpress.stackexchange.com/questions/407390/add-a-custom-woocommerce-settings-tab-with-sections
//https://stackoverflow.com/questions/72816886/add-a-custom-woocommerce-settings-page-including-page-sections
//https://gist.github.com/renventura/ff93a87f8779457b72ff4dc0366ba053/revisions
//https://felipeelia.com.br/como-criar-uma-tela-de-configuracao-para-o-seu-plugin-wordpress-com-a-settings-api-parte-2/
//https://www.tychesoftwares.com/how-to-add-custom-sections-fields-in-woocommerce-settings/
//https://www.speakinginbytes.com/2014/07/woocommerce-settings-tab/
//https://www.tychesoftwares.com/how-to-add-custom-sections-fields-in-woocommerce-settings/
//types https://github.com/woocommerce/woocommerce/blob/fb8d959c587ee95f543e682e065192553b3cc7ec/includes/admin/class-wc-admin-settings.php#L246

//******************************************************************************
//** Cria a tab "The Members" na página de configurações do Woocommerce
//******************************************************************************

add_filter( 'woocommerce_settings_tabs_array', 'add_woomembers_tab', 50 ); 
function add_woomembers_tab( $settings_tabs ) {
    $settings_tabs['woomembers'] = __( 'The Members', 'woocommerce' );

    return $settings_tabs;
}

//******************************************************************************
//** Adiciona uma section à tab "The Members"
//******************************************************************************
add_action( 'woocommerce_sections_woomembers', 'action_woocommerce_sections_woomembers', 10 );
function action_woocommerce_sections_woomembers() {
    global $current_section;
    $tab_id = 'woomembers';
    // Must contain more than one section to display the links
    // Make first element's key empty ('')
    $sections = array(
        ''   => __( '', 'woocommerce' ),
        //'produtos'   => __( 'Produtos', 'woocommerce' ),
    );
    echo '<ul class="subsubsub">';
    $array_keys = array_keys( $sections );
    foreach ( $sections as $id => $label ) {
        echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $tab_id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
    }
    echo '</ul><br class="clear" />';
}

//******************************************************************************
//** Adiciona as configurações 
//******************************************************************************
add_action( 'woocommerce_settings_woomembers', 'action_woocommerce_settings_woomembers', 10 );
function action_woocommerce_settings_woomembers() {
    // Call settings function
    $settings = get_woomembers_settings();
    WC_Admin_Settings::output_fields( $settings );  
}

//**************************************************************************************
//** Salva as configurações
//**************************************************************************************
add_action( 'woocommerce_settings_save_woomembers', 'action_woocommerce_settings_save_woomembers', 10 );
function action_woocommerce_settings_save_woomembers() {
    global $current_section;
    $tab_id = 'woomembers';
    // Call settings function
    $settings = get_woomembers_settings();
    WC_Admin_Settings::save_fields( $settings );
    if ( $current_section ) {
        do_action( 'woocommerce_update_options_' . $tab_id . '_' . $current_section );
    }
}

//**************************************************************************************
//** Cria as páginas de configurações na tab The Members do Woocommerce
//**************************************************************************************
add_action( 'woocommerce_settings_woomembers', 'get_woomembers_settings', 10 );
function get_woomembers_settings()
{
	$settings_woomembers = array();
	// Título
	$settings_woomembers[] = array( 
		'name' => __( 'Integração com a plataforma The Members', 'woocommerce' ),
		'type' => 'title',
		'desc' => __( 'Configurações para o cadastro automático dos clientes com a plataforma The Members após uma compra bem sucedida.', 'woocommerce' ),
		'id' => 'wc_woomembers_title'
	);
	// Token corporativo
	$settings_woomembers[] = array(
		'name'     => __( 'TOKEN da Plataforma', 'woocommerce' ),
		'id'       => 'wc_woomembers_token_do_cliente',
		'type'     => 'text',
		'desc'     => __( 'Insira aqui o token gerado na Plataforma (Configurações/Tokens).', 'woocommerce' ),
	);
	// Token do usuário desenvolvedor
	$settings_woomembers[] = array(
		'name'     => __( 'TOKEN da aplicação', 'woocommerce' ),
		'id'       => 'wc_woomembers_token_corporativo',
		'type'     => 'text',
		'desc'     => __( 'Insira aqui o token gerado pelo usuário desenvolvedor!', 'woocommerce' ),
	);
	// Trigger
	$settings_woomembers[] = array(
		'name'     => __( 'Trigger do pedido', 'woocommerce' ),
		'desc_tip'      => __( 'O cadastro será efetuado quando o status for este. Caso seja "Processsando" mudará para "Concluído" automaticamente se o cadastro for efetuado ou o cliente já existir no produto', 'woocommerce' ),
		'id'        => 'wc_woomembers_order_status_trigger',
		'class'     => 'wc-enhanced-select',
		'default'   => 'completed',
		'type'      => 'select',
		'options'   => array(
			'completed'     => __( 'Concluído', 'woocommerce' ),
			'processing'    => __( 'Processando', 'woocommerce' ),
		),
		//   'desc_tip' => true,
	);
	// Checkbox de notiicação de erro
	$settings_woomembers[] = array(
		'name'     => __( 'Notificação de erro', 'woocommerce' ),
		'id'       => 'wc_woomembers_error_notification',
		'type'     => 'checkbox',
		'desc'     => __( 'Receber notificação de erro por e-mail', 'woocommerce' ),
	);		
	// Checkbox de notificação de sucesso
	$settings_woomembers[] = array(
		'name'     => __( 'Notificação de sucesso', 'woocommerce' ),
		'id'       => 'wc_woomembers_success_notification',
		'type'     => 'checkbox',
		'desc'     => __( 'Receber notificação de sucesso por e-mail', 'woocommerce' ),
	);		
	// E-mail da notificação
	$settings_woomembers[] = array(
		'name'     => __( 'E-mail de notificação', 'woocommerce' ),
		'id'       => 'wc_woomembers_debug_email',
		'type'     => 'text',
	);

	$settings_woomembers[] = array( 'type' => 'sectionend', 'id' => 'woomembers' );
	return $settings_woomembers;
}



//******************************************************************************
//******************************************************************************
//	PÁGINA DE EDIÇÃO DE PRODUTOS
//******************************************************************************
//******************************************************************************

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
// Adiciona uma nova guia The Members às guias de opçoes de produto (product_data_tabs)
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_filter( 'woocommerce_product_data_tabs', 'adicionar_guia_woomembers' );
function adicionar_guia_woomembers($tabs) {
    $tabs['guia_woomembers'] = array(
        'label'    => __( 'The Members', 'text-domain' ),
        'target'   => 'woomembers_product_data',
        'class'    => array( 'show_if_simple', 'show_if_variable' ),
		// 'callback' => 'add_woomembers_custom_fields'
    );
    return $tabs;
}

// Artigos que me ajudaram:
// Excelente artigo https://kb.iwwa.com.br/como-criar-campos-personalizados-para-produtos-woocommerce/
// https://stackoverflow.com/questions/45911162/woocommerce-wp-select-options-array-from-product-attribute-terms
// https://stackoverflow.com/questions/43698813/in-woocommerce-product-options-edit-page-display-a-custom-field-before-the-sku

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_filter('woocommerce_product_data_panels','add_woomembers_custom_fields' );
function add_woomembers_custom_fields()
{
	global $woocommerce, $post, $product_object;
 
	echo '<div id="woomembers_product_data" class="panel woocommerce_options_panel">
    	  <div class="options_group">';
	
		  echo '<p>Opções de cadastro no The Members para o produto "<strong>'.$product_object->get_name().'</strong>":</p>';

	
	// Opções de cadastro no Produto

	$woomembers_check = $post->_woomembers_integration_check ? 'yes' : 'no';
	woocommerce_wp_checkbox( 
		array(
			'id'      		=> '_woomembers_integration_check',
			'value'			=> $woomembers_check,
			'label'   		=> __( 'Integrar à The Members?', 'woocommerce' ),
			'wrapper_class' => 'show_if_simple',
			// 'description'   => __( 'Insira aqui uma descrição.', 'woocommerce' ),
			// 'desc_tip' 		=> true
		)
	);
	
	echo '<div class=" woomembers_integration_options">';
	// caso o checkbox "Integrar ao The members?" não esteja marcado
	// para mostrar/esconder a div temos uma função js em /js/woomembers.js
	if ($woomembers_check != 'yes')
	{
		// 	esconde a div
		echo '<style>.woomembers_integration_options{display:none;}</style>';
	}
	
	woocommerce_wp_text_input( 
		array(
			'id'    	=> '_woomembers_integration_product',
			'label' 	=> __( 'Id do Produto na The Members', 'woocommerce' ),
			'value'   	=> $post->_woomembers_integration_product,
			// 'description' => 'Selecione o produto.',
			// 'desc_tip' 	=> true
		)
	);

	echo '</div>';
	echo '</div>';
	echo '</div>';

}

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** SALVA OS ITENS NO PRODUCT POST
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_action( 'woocommerce_process_product_meta', 'save_woomembers_custom_fields', 50);
function save_woomembers_custom_fields( $post_id )
{
	update_post_meta( $post_id, '_woomembers_integration_check', esc_attr( $_POST['_woomembers_integration_check'] ) );
	update_post_meta( $post_id, '_woomembers_integration_product', esc_attr( $_POST['_woomembers_integration_product'] ) );
}

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** CARREGA O SCRIPT JS PARA OS PARANAUÊS HIDE/SHOW NAS PÁGINAS ADMIN
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_action('admin_enqueue_scripts', 'import_woomembers_js_files');
function import_woomembers_js_files() 
{
	// Artigos / tutoriais sobre JS no WP
	// https://www.youtube.com/watch?v=A97tmGrMkYA

	//Arquivos JS carregam só na página de edição de produtos
	if (get_current_screen()->post_type == 'product')
	{
		//JS responsável pelo botão e mostra/esconde do checkbox
		wp_enqueue_script( 'woomembers', '/wp-content/plugins/woomembers/js/woomembers.js', array() , date("h:I:s"));
		//wp_localize_script( 'woomembers', 'ajax_woomembers_object', array('url' => admin_url('admin-ajax.php')) );
		// ^^^^ Retirado pois não precisamos fazer requisições ajax lá no JS ^^^^
	}
}


//******************************************************************************
//******************************************************************************
//				 CADASTRO DE CLIENTES NO WOOMEMBERS
//******************************************************************************
//******************************************************************************
//		usando HOOK de mudança de Status do Pedido para 
//		PROCESSANDO ou CONCLUIDO (quando um pagamento é concluído,
//		o pedido sai do status AGUARADANDO para PROCESSANDO)
//		então, automaticamente o cliente é cadadstrado na plataforma
//		The Members através de um chamado HTTP POST 
//******************************************************************************
//******************************************************************************

//add_action('woocommerce_order_status_completed', 'woocommerce_to_LeadLovers_customer_register_on_product', 10, 1); 
add_action('woocommerce_order_status_changed', 'add_user_to_woomembers', 10, 3);
function add_user_to_woomembers($order_id, $old_status, $new_status)
{
	//testa se o trigger definido nas configurações e o status do pedido são os mesmos
	if($new_status == get_option('wc_woomembers_order_status_trigger'))
	{
		//----------------------------------
		//  SALVA OS DADOS DO PEDIDO
		//----------------------------------
		$order = wc_get_order($order_id);
		// Tratamento dos dados do cliente extraídos do pedido (order_id)
		$customer_email = $order->get_billing_email();
		$customer_name = $order->get_billing_first_name();
		$customer_last_name = $order->get_billing_last_name();
		$customer_cpf = $order->get_meta('_billing_cpf');
		
		//Pega o telefone e insere o +55 se estiver no Brasil (a máscara para o BR exige o DDD mas impede o cód internacional+55)
		$customer_phone = $order->get_billing_phone();
		if( $order->get_billing_country() == 'BR' )
		{
			$customer_phone = '+55 ' . $customer_phone;
		}
		//Pega a data de e formata para AAAA-MM-DD
		$date = new DateTime($order->get_date_completed());
		$customer_date_order_completed = $date->format('Y-m-d');
		$items = $order->get_items();
		foreach ( $items as $item )
		{
			$customer_product = $item->get_product();
			$customer_product_id = $customer_product->get_meta('_woomembers_integration_product');
			$customer_product_name = $customer_product->get_name();
			
			//verifica se o produto está integrado
			if('yes' == $customer_product->get_meta('_woomembers_integration_check'))
			{
				// ----------------------------------------------------
				// CADASTRAR NO PRODUTO
				// ----------------------------------------------------
				
				//----- wp_remote_post Documentation https://developer.wordpress.org/reference/functions/wp_remote_post/	
				$url_base = 'https://registration.themembers.dev.br/api';
				$token_do_cliente = get_option('wc_woomembers_token_do_cliente');  
				$token_corporativo = get_option('wc_woomembers_token_corporativo');  
				
				//URL_BASE/users/create/{token_corporativo}/{token_do_cliente}
				$endpoint = '/users/create/' . $token_corporativo . '/' . $token_do_cliente;
				$response = wp_remote_post( $url_base . $endpoint, array(
					'method'      => 'POST',
					'timeout'     => 30,
					'redirection' => 10,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array('accept: application/json'),
					'body'        => array(
						'product_id' => array($customer_product_id),
						'users' => array(
							array(
								'name' 		=> $customer_name,
								'last_name' => $customer_last_name,
								'email' 	=> $customer_email,
								//'password' => "123456", // dado opcional
								'document' 	=> $customer_cpf,
								'phone'		=> $customer_phone,
								//"reference_id": "789456123546", 
								'accession_date' => $customer_date_order_completed
							)
						)
					)
				));

				if ( is_wp_error( $response ) ) 
				{
					$error_message = $response->get_error_message();
					$order->add_order_note("Erro na chamada http do cadastro da The Members: " . $error_message);
					$error_notification_message = "Produto: " . $customer_product_name . " => Erro na chamada http do cadastro da The Members: " . $error_message . '<br>';
				} 
				else
				{
					// pegamos o "corpo" da resposta recebida...
					$response_body = wp_remote_retrieve_body( $response );
					// e transformamos de JSON em um array PHP normal.
					$response_data = json_decode( $response_body, true );

					//envia uma resposta nas notas do pedido 
					$order->add_order_note('Cadastro na The Members: <br>' . 
						'Resposta: ' . wp_remote_retrieve_response_code( $response ) . ' ' . wp_remote_retrieve_response_message( $response ) . '<br>'.
						'Mensagem: ' .	$response_data['message']
					);

					//Cuidamos de cada caso de resposta
					switch ( wp_remote_retrieve_response_code( $response ) )
					{
						case 200:		//OK
							//Salva mensagem de sucesso
							$success_notification_message .= "Produto: " . $customer_product_name . " => Cadastro na The Members realizado com sucesso. Resposta da plataforma: " . $response_data['message'] . '<br>';
									
							//caso o trigger seja no status "Processando" muda automativamente para "Concluído"
							if( get_option('wc_woomembers_order_status_trigger') == 'processing' )
							{
								$order->update_status( 'completed' );
							}
							
							break;

						case 400:		//bad request
						case 401:		//unauthorized
						default:		// Algum erro estrutural na mensagem...
							//Salva mensagem de erro
							$error_notification_message .= "Produto: " . $customer_product_name . " => Cadastro na The Members FALHOU... <br>Erro " . wp_remote_retrieve_response_code( $response ) . ': ' . wp_remote_retrieve_response_message( $response ) . '<br>';
					}
				}
			}
		}
			
		// ----------------------------------------------------
		// PROCESSA NOTIFICAÇÕES
		// ----------------------------------------------------

		if ($success_notification_message || $error_notification_message)
		{
			$send_success = get_option('wc_woomembers_success_notification');
			$send_error = get_option('wc_woomembers_error_notification');
			if(($send_success == 'yes' && $success_notification_message) || ($send_error == 'yes' && $error_notification_message))
			{
				$subject = 'Cadastro <strong>The Members</strong>';

				$email_body .= $success_notification_message;
				$email_body .= $error_notification_message;
				$email_body .= "<br>-------------------------<br>" . 
				$email_body .= " DADOS DO PEDIDO<br>" . 
				$email_body .= "-------------------------<br>" . 
				"Nome: " . $order->get_formatted_billing_full_name() . '<br>' . 
				"E-mail: " . $customer_email . '<br>' . 
				"Telefone: " . $customer_phone . '<br>' . 
				"Documento: " . $customer_cpf . '<br>' . 
				"Pedido: " . $order_id . '<br>';
				//seleciona o e-mail cadastrado na página de configurações do plugin
				$to = get_option( 'wc_woomembers_debug_email' );
				//definição padrão de cabeçalho do e-mail
				$headers = array( 'Content-Type: text/html; charset=UTF-8' );
				//envia o e-mail
				wp_mail( $to, $subject, $email_body, $headers );
			}
		}
	}
}



//--------------------------------------------
//---- D E B U G ---- Deixo aqui para facilitar o teste de valores através de notices
//--------------------------------------------

//wc_add_notice( string $message, string $notice_type = 'success', array $data = array() )

/* function debug_admin_notice() {
	$class = 'notice notice-info';
	//$message = get_post_meta( $post->ID, 'woocommerce_sku', true );//"Check?  " . get_option( 'woocommerce_woomembers_integration_product' );
	//$mesage = get_template_directory_uri();
	$message = ;
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}
add_action( 'admin_notices', 'debug_admin_notice' ); */
