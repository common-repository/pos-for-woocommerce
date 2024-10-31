<?php if ( ! defined( 'ABSPATH' ) ) exit; 
 
class posmalaysia_Settings {

	public $accountDetail;
	public function __construct() {
		$this->define_hooks();
		$this->accountDetail= new Account_Api_PosMalaysia();
		$this->update_posAccountDetail($option_name='', $old_value='', $value='');
	}

    protected function define_hooks() {


		add_filter( 'woocommerce_get_sections_shipping', array( $this, 'posmalaysia_add_settings_tab' ) );
		add_filter( 'woocommerce_get_settings_shipping' , array( $this, 'posmalaysia_get_settings' ) , 10, 2 );
		add_action('update_option_posmalaysia_Apikey',  array( $this,'update_posAccountDetail'),10,3);
		add_action('update_option_posmalaysia_secret_key',  array( $this,'update_posAccountDetail'),10,3);
		add_action('update_option_posmalaysia_sender_name',  array( $this,'update_posAccountDetail'),10,3);
	}

	public function update_posAccountDetail($option_name, $old_value, $value){
	
			$accountDetail=$this->accountDetail->account();
			$posAccountNo=$accountDetail['data']['accountNo'];
			$poscompanyName=$accountDetail['data']['companyName'];
			$posemail=$accountDetail['data']['email'];
			$posphone=$accountDetail['data']['phone'];

			$data=array(
				'PosAccountNo'=>$posAccountNo,
				'PosCompanyname'=>$poscompanyName,
				'posmalaysia_sender_email'=>$posemail,
				'posmalaysia_sender_phone'=>$posphone
		);

		
			update_option('PosAccountDetail',$data,true);
			update_option('PosAccountNo',$posAccountNo,true);
			
	}
    public function posmalaysia_add_settings_tab( $settings_tab ){
	    $settings_tab['posmalaysia'] = __( 'Pos Malaysia' );
	    return $settings_tab;
	}

    public function posmalaysia_get_settings( $settings, $current_section ) {
			
		$accountDetail=$this->accountDetail->account();
		if(isset($accountDetail['error'])){
			echo $accountDetail['error'];
		}
		//print_r($accountDetail);
		$PosAccountDetail=get_option('PosAccountDetail');
		$PosAccountNo=$PosAccountDetail['PosAccountNo'];
		$PosCompanyname=$PosAccountDetail['PosCompanyname'];
		$posmalaysiasenderemail=$PosAccountDetail['posmalaysia_sender_email'];
		$posmalaysia_sender_phone=$PosAccountDetail['posmalaysia_sender_phone'];
        $custom_settings = array();
        if( 'posmalaysia' == $current_section ) {
            $custom_settings =  array(
				array(
			        'name' => __( 'Pos Malaysia' ),
			        'type' => 'title',
			        'desc' => __( 'Account Settings' ),
			        'id'   => 'PosPlug_Settings' ,
				),
				array(
					'name' => __( 'Account No' ),
					'type' => 'text',
					//'desc'  => __( 'Please contact Pos Malaysia KAM to get Account No' ),
					'id'	=> 'PosAccountDetail["PosAccountNo"]',
					'value'=>$PosAccountNo,
					//'custom_attributes' => [ 'required' => 'required' ],
					'custom_attributes'=> [ 'readonly' => 'readonly' ],
				),
				array(
					'name' => __( 'Company Name' ),
					'type' => 'text',
					'desc'  => __( 'Company name with registered account number' ),
					'id'	=> 'PosAccountDetail["PosCompanyname"]',
					'custom_attributes'=> [ 'readonly' => 'readonly' ],
					'value'=>$PosCompanyname,
					'custom_attributes'=> [ 'readonly' => 'readonly' ],
					//'custom_attributes' => [ 'required' => 'required' ],
				),
				array(
					'name' => __( 'Public Key' ),
					'type' => 'password',
					'desc' => __( 'Please generate the key in SendParcel Pro' ),
					'id' 	=> 'posmalaysia_Apikey',
					'custom_attributes' => [ 'required' => 'required' ],
				),
				array(
					'name' => __( 'Secret Key' ),
					'type' => 'password',
					'desc' => __( 'Please generate the key in SendParcel Pro' ),
					'id' 	=> 'posmalaysia_secret_key',
					'custom_attributes' => [ 'required' => 'required' ],
				),
				array(
					'name' => __( 'Store Name' ),
					'type' => 'text',
					'desc'=>__('Please insert the same store name created in SendParcel Pro'),
					'id'   => 'posmalaysia_sender_name',
					'custom_at tributes' => [ 'required' => 'required' ],
				),
				array(
					'name' => __( 'Store Email' ),
					'type' => 'text',
					'id'   => 'PosAccountDetail["posmalaysia_sender_email"]',
					'value'=>$posmalaysiasenderemail,
					'custom_attributes'=> [ 'readonly' => 'readonly' ],
					//'custom_attributes' => [ 'required' => 'required' ],
				),
				array(
					'name' => __( 'Store Phone Number' ),
					'type' => 'tel',
					'id'   => 'PosAccountDetail["posmalaysia_sender_phone"]',
					'custom_attributes'=> [ 'readonly' => 'readonly' ],
					//'custom_attributes' => [ 'required' => 'required' ],
					'value'=>$posmalaysia_sender_phone,
					
					
                ),
				array(
			        'name' => __( 'Auto generate Consignment number' ),
			        'type' => 'checkbox',
			        'desc' => __( 'Please mark tick if you want to auto push orders to Pos Malaysia.' ),
			        'id'   => 'posmalaysia_auto_connote' ,
				),
            
				array( 'type' => 'sectionend', 'id' => 'PosPlug_Settings' ),
				
			);
			
	       	return $custom_settings;
       	} else {
			 	return $settings;
       	}
	}
}