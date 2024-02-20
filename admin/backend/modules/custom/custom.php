<?php

require 'classes/twilio/vendor/autoload.php';
use Twilio\Rest\Client;

/**
 * Handles some custom functionality for backend
 */
class custom extends Backend {

	public function __construct() {
		$this->M_DIR = 'backend/modules/custom/';
		parent::__construct ();
	}

    /**
     * @param int[] $data
     * @return array
     * @throws phpmailerException
     */
    public function memberFolderDisplay($data = array('id'=>0)) {
		$p = $this->fetchAll(sprintf("select p.*,po.product_id as opt_id from custom_member_product_options po, product p where po.member_id = %d and p.id = po.product_id and po.isgroup = 1 and p.enabled = 1 and p.published = 1 order by p.name",$data["id"]));
		$recs = array();
		$f = new Forms();
		$f->init($this->M_DIR."forms/memberProduct.html");
		$flds = array(
			'id'=>array('type'=>'hidden','name'=>'custom_member_product_options[]','value'=>'%%opt_id%%')
		);
		$flds = $f->buildForm($flds);
		foreach($p as $key=>$value) {
			$f->addData($value);
			$recs[] = $f->show();
		}
		return array(
			'form'=>file_get_contents($this->M_DIR.'forms/memberFolder.html'),
			'description'=>'KJV Parameters',
			'fields'=>array(
				'custom_fuel'=>array('type'=>'productOptGroup','required'=>true,'onchange'=>'getFuel();','id'=>'custom_fuel','value'=>'0','prettyName'=>'Fuel'),
				'custom_fuel_override'=>array('type'=>'textfield','required'=>true,'validation'=>'number','required'=>true,'onchange'=>'getFuel();','id'=>'custom_fuel_override','value'=>0,'class'=>'a-right def_field_small','value'=>'0','prettyName'=>'Fuel Override'),
				'custom_insurance_override'=>array('type'=>'textfield','required'=>true,'validation'=>'number','required'=>true,'onchange'=>'getInsurance();','id'=>'custom_insurance_override','value'=>0,'class'=>'a-right def_field_small','prettyName'=>'Insurance Override'),
				'custom_weight'=>array('type'=>'productOptGroup','required'=>true,'onchange'=>'getWeight();','id'=>'custom_weight','prettyName'=>'Weight'),
				'custom_weight_override'=>array('type'=>'textfield','required'=>true,'validation'=>'number','required'=>true,'onchange'=>'getWeight();','id'=>'custom_weight_override','value'=>0,'class'=>'a-right def_field_small','prettyName'=>'Weight Override'),
				'custom_free_weight'=>array('type'=>'textfield','required'=>true,'validation'=>'number','required'=>true,'id'=>'custom_free_weight','value'=>0,'class'=>'a-right def_field_small','prettyName'=>'Free Weight'),
				'custom_package_types'=>array('type'=>'select','required'=>true,'sql'=>'select id,title from product_folders where enabled = 1 order by title','prettyName'=>'Package Types'),
				'custom_additional_services'=>array('type'=>'select','required'=>true,'options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'prettyName'=>'Additional Services','reformatting'=>false)
			)
		);
	}

    /**
     * @param int[] $data
     * @return array
     * @throws phpmailerException
     */
    public function memberDisplay(&$data = array('id'=>0,'custom_parent_org'=>0)) {
		$p = $this->fetchAll(sprintf("select p.*,po.product_id as opt_id from custom_member_product_options po, product p where po.member_id = %d and p.id = po.product_id and po.isgroup = 0 and p.enabled = 1 and p.published = 1 order by p.name",$data["id"]));
		$recs = array();
		$f = new Forms();
		$f->init($this->M_DIR."forms/memberProduct.html");
		$flds = array(
			'id'=>array('type'=>'hidden','name'=>'custom_member_product_options[]','value'=>'%%opt_id%%')
		);
		$flds = $f->buildForm($flds);
		foreach($p as $key=>$value) {
			$f->addData($value);
			$recs[] = $f->show();
		}
		$this->logMessage(__FUNCTION__,sprintf("recs [%s]",print_r($recs,true)),3);
		$return = array(
			'form'=>file_get_contents($this->M_DIR.'forms/member.html'),
			'description'=>'KJV Parameters',
			'fields'=>array(
				'custom_weight_override'=>array('type'=>'textfield','required'=>true,'validation'=>'number','required'=>true,'onchange'=>'getWeight();','id'=>'custom_weight_override','value'=>0,'class'=>'a-right def_field_small'),
				//'custom_km_override'=>array('type'=>'textfield','required'=>true,'validation'=>'number','required'=>true,'onchange'=>'getKm();','id'=>'custom_km_override','prettyName'=>'Km Override','value'=>0,'class'=>'a-right def_field_small'),
				'custom_free_weight'=>array('type'=>'textfield','required'=>true,'validation'=>'number','required'=>true,'id'=>'custom_free_weight','prettyName'=>'Free Weight','value'=>0,'class'=>'a-right def_field_small'),
				'custom_parent_org'=>array('type'=>'select','sql'=>sprintf("select id, concat(company,' ',firstname,' ',lastname) from members where id = %d",array_key_exists('custom_parent_org',$data) ? $data['custom_parent_org'] : 0),'onchange'=>'getCustomer(this);','prettyName'=>'Parent Organization'),
				'custom_fuel_override'=>array('type'=>'textfield','prettyName'=>'Fuel Surcharge','onchange'=>'getFuel();','id'=>'custom_fuel_override','class'=>'a-right def_field_small','value'=>'0'),
				'custom_fuel_exempt'=>array('type'=>'checkbox','required'=>true,'value'=>1,'id'=>'custom_charge_fuel','class'=>'form-control','prettyName'=>'Charge Fuel'),
				'custom_by_km'=>array('type'=>'checkbox','prettyName'=>'By Km','value'=>1),
				'subsidiary_orgs'=>array('type'=>'select','multiple'=>true,'required'=>false,'database'=>false,'sql'=>sprintf("select id, concat(company,' ',firstname,' ',lastname) from members where custom_parent_org = %d",array_key_exists('custom_parent_org',$data) ? $data['id'] : -1),'class'=>'def_field_ddl'),
				'custom_zones'=>array('name'=>'custom_zones','type'=>'select','required'=>true,'options'=>$this->nodeSelect(0, 'zone_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Zone Structure'),
				'custom_super_user'=>array('type'=>'checkbox','value'=>1,'prettyName'=>'Super User'),
				'custom_insurance_override'=>array('type'=>'textfield','required'=>true,'validation'=>'number','required'=>true,'onchange'=>'getInsurance();','id'=>'custom_insurance_override','value'=>0,'class'=>'a-right','class'=>'a-right def_field_small'),
				'custom_on_account'=>array('type'=>'checkbox','value'=>1),
				'custom_cc_charge'=>array('type'=>'checkbox','value'=>1),
				'custom_downtown_surcharge'=>array('type'=>'checkbox','value'=>1),
				'custom_pickup_notification'=>array('type'=>'checkbox','value'=>1,'required'=>false),
				'custom_delivery_notification'=>array('type'=>'checkbox','value'=>1,'required'=>false),
				'custom_additional_services'=>array('type'=>'select','required'=>false,'options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'prettyName'=>'Additional Services','reformatting'=>false),
				'custom_qb_id'=>array('type'=>'number','required'=>false,'min'=>0,'class'=>'def_field_input a-right'),
				'custom_invoice_email'=>array('type'=>'textfield','required'=>false,'class'=>'def_field_long'),
				'custom_pickup_emails'=>array('type'=>'textfield','required'=>false,'class'=>'def_field_long'),
				'custom_delivery_emails'=>array('type'=>'textfield','required'=>false,'class'=>'def_field_long'),
				'custom_consolidate_invoices'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'custom_reference_number'=>array('type'=>'textfield','required'=>false),
				'custom_free_item_count'=>array('type'=>'number','required'=>true,'value'=>0,'class'=>'def_field_input a-right'),
				'custom_free_item_charge'=>array('type'=>'number','required'=>true,'value'=>0.00,'class'=>'def_field_input a-right','step'=>'.01'),
				'optional_required_fields'=>array('type'=>'select','idlookup'=>'optional_required_fields','multiple'=>true,'required'=>false,'database'=>false)
			)
		);
		$data["optional_required_fields"] = $this->fetchScalarAll(sprintf("select optional_id from member_optional_fields where member_id = %d", $data["id"]));
		return $return;
	}

    /**
     * @param int[] $data
     * @param $form
     * @return array
     * @throws phpmailerException
     */
    public function productDisplay(&$data = array('id'=>0,'custom_availability'=>0), &$form) {
		$tmp = array();
		for($idx = 0; $idx < 7; $idx++) {
			if ($data["custom_availability"] & pow(2,$idx)) $tmp[] = pow(2,$idx);
		}
		$data["custom_availability"] = $tmp;
		$form->addData($data);
		$this->logMessage(__FUNCTION__,sprintf("form is now [%s] from [%s]",print_r($form,true),print_r($data,true)),1);
		return array(
			'form'=>file_get_contents($this->M_DIR.'forms/product.html'),
			'description'=>'KJV Parameters',
			'fields'=>array(
				'custom_minimum_charge'=>array('type'=>'textfield','required'=>true,'value'=>'0','validate'=>'number'),
				'custom_available_to_all'=>array('type'=>'checkbox','required'=>false,'value'=>'1'),
				'custom_zone_surcharge'=>array('type'=>'textfield','required'=>true,'value'=>'0','validate'=>'number'),
				'custom_downtown_surcharge'=>array('type'=>'textfield','required'=>true,'value'=>'0','validate'=>'number'),
				'custom_km_mincharge'=>array('type'=>'textfield','required'=>true,'value'=>'0','validate'=>'number'),
				'custom_km_maxcharge'=>array('type'=>'textfield','required'=>true,'value'=>'0','validate'=>'number'),
				'custom_km_charge'=>array('type'=>'textfield','required'=>true,'value'=>'0','validate'=>'number'),
				'custom_same_day'=>array('type'=>'checkbox','required'=>false,'value'=>'1'),
				'custom_delivery_formula'=>array('type'=>'textfield','required'=>false),
				'custom_delivery_relative'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'custom_pickup_start'=>array('type'=>'timepicker','required'=>true,'ampm'=>true),
				'custom_cutoff_time'=>array('type'=>'timepicker','required'=>true,'ampm'=>true),
				'custom_availability'=>array('name'=>'custom_availability','type'=>'select','multiple'=>true,'required'=>true,'lookup'=>'daymask','database'=>false),
				'custom_commission'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'custom_driver_split'=>array('type'=>'textfield','required'=>true,'value'=>80,'validation'=>'number'),
				'custom_biker_split1'=>array('type'=>'textfield','required'=>true,'value'=>20,'validation'=>'number'),
				'custom_biker_split2'=>array('type'=>'textfield','required'=>true,'value'=>20,'validation'=>'number'),
				'custom_out_of_zone_rate'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number'),
				'is_fedex'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'custom_fedex'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number'),
				'fedex_package_type'=>array('type'=>'select','required'=>false,'lookup'=>'fedex_package_types'),
				'custom_has_fuel_surcharge'=>array('type'=>'checkbox','required'=>true,'value'=>1),
				'custom_special_requirement'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'custom_inter_downtown'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number'),
				'custom_split_allocation'=>array('type'=>'select','lookup'=>'split_allocation','required'=>true),
				'custom_color_coding'=>array('type'=>'select','lookup'=>'color_coding','required'=>false)
			)
		);
	}

    /**
     * @param $data
     * @param $request
     * @return bool
     * @throws phpmailerException
     */
    public function productUpdate($data, $request) {
		$upd = array("custom_availability"=>0);
		if (array_key_exists("custom_availability",$request)) {
			foreach($request["custom_availability"] as $key=>$value) {
				$upd["custom_availability"] |= $value;
			}
		}
		$stmt = $this->prepare(sprintf("update product set %s=? where id = %d",implode("=?, ",array_keys($upd)),$data["id"]));
		$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
		return $stmt->execute();
	}

    /**
     * @param $cart
     * @return mixed
     * @throws phpmailerException
     */
    public function postRecalc($cart) {
		$value = 0;
		foreach($cart["products"] as $key=>$line) {
			if ($this->fetchScalar(sprintf("select custom_commission from product where id = %d",$line["product_id"])))
				$value += $line["total"];
		}
		$cart["header"]["custom_commissionable_amt"] = $value;
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($cart,true)),1);
		return $cart;
	}

    /**
     * @param $cart
     * @return mixed
     * @throws phpmailerException
     */
    public function preRecalc($cart) {
		$cart["header"]["freeShipping"] = 0;
		if ($fuel = $this->fetchSingle(sprintf("select p.* from members_folders mf, members_by_folder mbf, product p where mbf.member_id = %d and mf.id = mbf.folder_id and p.id = mf.custom_fuel and mf.id = %d limit 1", $cart["header"]["member_id"], MSH_GROUP))) {
			//
			//	Mount Sinai Group - no fuel charge is between specific postal codes
			//
			if ($this->checkArray("addresses:shipping:postalcode",$cart) && $this->checkArray("addresses:pickup:postalcode",$cart) && strpos(MSH_NO_FUEL,sprintf("|%s|", substr($cart["addresses"]["shipping"]["postalcode"],0,3))) !== FALSE &&
					strpos(MSH_NO_FUEL,sprintf("|%s|", substr($cart["addresses"]["pickup"]["postalcode"],0,3))) !== FALSE) {
				foreach($cart["products"] as $k=>$v) {
					if ($v["product_id"] == $fuel["id"]) {
						$cart["products"][$k]["price"] = 0;
						$cart["products"][$k]["value"] = 0;
						$cart["products"][$k] = Ecom::lineValue($cart["products"][$k]);
						$this->execute(sprintf("delete from order_taxes where order_id = %d and line_id = %d", array_key_exists("order_id",$cart["header"]) ? $cart["header"]["order_id"] : $cart["products"][$k]["order_id"],$cart["products"][$k]["line_id"]));
						$tmp = $cart["products"][$k];
						unset($tmp["taxdata"]);
						unset($tmp["order_id"]);
						unset($tmp["line_id"]);
						unset($tmp["id"]);
						$tbr = $this->fetchSingle(sprintf("select * from order_lines limit 1"));
						foreach($tmp as $sk=>$sv) {
							if (!array_key_exists($sk,$tbr)) {
								$this->logmessage(__FUNCTION__,sprintf("^^^ removing key [%s]", $sk),1);
								unset($tmp[$sk]);
							}
						}
						$stmt = $this->prepare(sprintf("update order_lines set %s = ? where id = %d", implode(" = ?, ",array_keys($tmp)), $cart["products"][$k]["id"]));
						$stmt->bindParams(array_merge(array(str_repeat("s",count($tmp))), array_values($tmp)));
						$stmt->execute();
						$this->logMessage(__FUNCTION__, sprintf("line [%s]", print_r($cart["products"][$k],true)),1);
					}
				}
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('returned cart [%s]',print_r($cart,true)),2);
		return $cart;
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function getFuel() {
		$f = array_key_exists('f',$_REQUEST) ? $_REQUEST['f'] : 0;
		$o = array_key_exists('o',$_REQUEST) ? $_REQUEST['o'] : 0;
/*
		if ($p = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",$f))) {
			$price = $p["price"];
			$override = ($p["price"] + $o);
		}
		else {
			$price = 0.00;
			$override = 0.00;
		}
*/
		if ($p = $this->fetchSingle(sprintf("select * from product where id = %d",$f))) {
			$price = $p["custom_minimum_charge"];
			$override = ($p["custom_minimum_charge"] + $o);
		}
		else {
			$price = 0.00;
			$override = 0.00;
		}
		$form = new Forms();
		$form->init($this->M_DIR."forms/getFuel.html");
		$form->addTag("price",sprintf("%.2f%%",$price));
		$form->addTag("override",sprintf("%.2f%%",$override));
		return $this->ajaxReturn(array('html'=>$form->show(),'status'=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function getInsurance() {
		$f = INSURANCE;
		$o = array_key_exists('o',$_REQUEST) ? $_REQUEST['o'] : 0;
/*
		if ($p = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",$f))) {
			$price = $p["price"];
			$override = $p["price"] + $o;
		}
		else {
			$price = 0.00;
			$override = 0.00;
		}
*/
		if ($p = $this->fetchSingle(sprintf("select * from product where id = %d",$f))) {
			$price = $p["custom_minimum_charge"];
			$override = $p["custom_minimum_charge"] + $o;
		}
		else {
			$price = 0.00;
			$override = 0.00;
		}
		$form = new Forms();
		$form->init($this->M_DIR."forms/getInsurance.html");
		$form->addTag("price",sprintf("%.2f%%",$price));
		$form->addTag("override",sprintf("%.2f%%",$override));
		return $this->ajaxReturn(array('html'=>$form->show(),'status'=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function getWeight() {
		$f = array_key_exists('f',$_REQUEST) ? $_REQUEST['f'] : 0;
		$o = array_key_exists('o',$_REQUEST) ? $_REQUEST['o'] : 0;
		if ($p = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",$f))) {
			$price = $p["price"];
			$override = $p["price"] * (1+$o/100);
		}
		else {
			$price = 0.00;
			$override = 0.00;
		}
		$form = new Forms();
		$form->init($this->M_DIR."forms/getWeight.html");
		$form->addTag("price",$this->my_money_format($price));
		$form->addTag("override",$this->my_money_format($override));
		return $this->ajaxReturn(array('html'=>$form->show(),'status'=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function getKm() {
		$f = array_key_exists('f',$_REQUEST) ? $_REQUEST['f'] : 0;
		$o = array_key_exists('o',$_REQUEST) ? $_REQUEST['o'] : 0;

/*
		if ($p = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",$f))) {
			$price = $p["price"];
			$override = $p["price"] * (1+$o/100);
		}
		else {
			$price = 0.00;
			$override = 0.00;
		}
*/
		if ($p = $this->fetchSingle(sprintf("select * from product where id = %d",$f))) {
			$price = $p["custom_minimum_charge"];
			$override = $p["custom_minimum_charge"] * (1+$o/100);
		}
		else {
			$price = 0.00;
			$override = 0.00;
		}
		$form = new Forms();
		$form->init($this->M_DIR."forms/getKm.html");
		$form->addTag("price",$this->my_money_format($price));
		$form->addTag("override",$this->my_money_format($override));
		return $this->ajaxReturn(array('html'=>$form->show(),'status'=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function getMemberWeight() {
		$m = array_key_exists('m',$_REQUEST) ? $_REQUEST['m'] : 0;
		$o = array_key_exists('o',$_REQUEST) ? $_REQUEST['o'] : 0;
		$price = 0.00;
		$override = 0.00;
		if ($m == 0) {
			$override = "Member must be saved first";
		}
		else {
			$override = "Could not get group pricing";
			if ($f = $this->fetchSingle(sprintf("select f.* from members_folders f, members_by_folder mf where mf.member_id = %d and f.id = mf.folder_id order by id limit 1",$m))) {
				if ($p = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",$f['custom_weight']))) {
					$price = $p["price"];
					//
					//	group override first
					//
					$override = $this->my_money_format($p["price"] * (1+$f["custom_weight_override"]/100+$o/100));
					//
					//	member override on top
					//
					//$override = $this->my_money_format($override * (1+$o/100));
				}
			}
		}
		$form = new Forms();
		$form->init($this->M_DIR."forms/getMemberWeight.html");
		$form->addTag("override",$override);
		return $this->ajaxReturn(array('html'=>$form->show(),'status'=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function getMemberKm() {
		$m = array_key_exists('m',$_REQUEST) ? $_REQUEST['m'] : 0;
		$o = array_key_exists('o',$_REQUEST) ? $_REQUEST['o'] : 0;
		$price = 0.00;
		$override = 0.00;
		if ($m == 0) {
			$override = "Member must be saved first";
		}
		else {
			$override = "Could not get group pricing";
			if ($f = $this->fetchSingle(sprintf("select f.* from members_folders f, members_by_folder mf where mf.member_id = %d and f.id = mf.folder_id order by id limit 1",$m))) {
				//if ($p = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",$f['custom_km']))) {
				if ($p = $this->fetchSingle(sprintf("select * from product where id = %d",$f['custom_km']))) {
					$price = $p["custom_minimum_charge"];
					$override = $this->my_money_format($p["custom_minimum_charge"] * (1+$f["custom_km_override"]/100+$o/100));
				}
			}
		}
		$form = new Forms();
		$form->init($this->M_DIR."forms/getMemberKm.html");
		$form->addTag("override",$override);
		return $this->ajaxReturn(array('html'=>$form->show(),'status'=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function getMemberFuel() {
		$m = array_key_exists('m',$_REQUEST) ? $_REQUEST['m'] : 0;
		$o = array_key_exists('o',$_REQUEST) ? $_REQUEST['o'] : 0;
		$price = 0.00;
		$override = 0.00;
		if ($m == 0) {
			$override = "Member must be saved first";
		}
		else {
			$override = "Could not get group pricing";
			if ($f = $this->fetchSingle(sprintf("select f.* from members_folders f, members_by_folder mf where mf.member_id = %d and f.id = mf.folder_id order by id limit 1",$m))) {
				//if ($p = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",$f['custom_fuel']))) {
				if ($p = $this->fetchSingle(sprintf("select * from product where id = %d",$f['custom_fuel']))) {
					$price = $p["custom_minimum_charge"];
					$override = sprintf("%s%%", number_format($p["custom_minimum_charge"] + $f["custom_fuel_override"] + $o, 2) );
				}
			}
		}
		$form = new Forms();
		$form->init($this->M_DIR."forms/getMemberFuel.html");
		$form->addTag("override",$override);
		return $this->ajaxReturn(array('html'=>$form->show(),'status'=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function getMemberInsurance() {
		$m = array_key_exists('m',$_REQUEST) ? $_REQUEST['m'] : 0;
		$o = array_key_exists('o',$_REQUEST) ? $_REQUEST['o'] : 0;
		$price = 0.00;
		$override = 0.00;
		if ($m == 0) {
			$override = "Member must be saved first";
		}
		else {
			$override = "Could not get group pricing";
			if ($f = $this->fetchSingle(sprintf("select f.* from members_folders f, members_by_folder mf where mf.member_id = %d and f.id = mf.folder_id order by id limit 1",$m))) {
				//if ($p = $this->fetchSingle(sprintf("select * from product_pricing where product_id = %d order by min_quantity limit 1",INSURANCE))) {
				if ($p = $this->fetchSingle(sprintf("select * from product where id = %d",INSURANCE))) {
					$price = $p["custom_minimum_charge"];
					$override = $p["custom_minimum_charge"] + $f["custom_insurance_override"] +	$o;
				}
			}
		}
		$form = new Forms();
		$form->init($this->M_DIR."forms/getMemberInsurance.html");
		$form->addTag("price",sprintf("%.2f%%",$price));
		$form->addTag("override",sprintf("%.2f%%",$override));
		return $this->ajaxReturn(array('html'=>$form->show(),'status'=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function memberAddProduct() {
		$m = array_key_exists('m',$_REQUEST) ? $_REQUEST['m'] : 0;
		$p = array_key_exists('p',$_REQUEST) ? $_REQUEST['p'] : array();
		$g = array_key_exists('g',$_REQUEST) ? $_REQUEST['g'] : 0;
		$f = new Forms();
		$f->init($this->M_DIR."forms/memberProduct.html");
		$flds = array(
			'id'=>array('type'=>'select','sql'=>sprintf('select id,name from product where enabled = 1 and published = 1 and deleted = 0 and custom_available_to_all = 0 and id not in (%s) order by name',implode(",",array_merge(array(0),$p))),'required'=>false,'name'=>'custom_member_product_options[]')
		);
		$flds = $f->buildForm($flds);
		return $this->ajaxReturn(array('status'=>true,'html'=>$f->show()));
	}

    /**
     * @param $data
     * @param $request
     * @return void
     * @throws phpmailerException
     */
    public function memberUpdate($data, $request) {
		if (array_key_exists('subsidiary_orgs',$request)) {
			$this->execute(sprintf("update members set custom_parent_org = 0 where custom_parent_org = %d and id not in (%s)", $data["id"], implode(",",$request["subsidiary_orgs"])));
		}
		else $this->execute(sprintf("update members set custom_parent_org = 0 where custom_parent_org = %d",$data["id"]));
		$recs = array();
		if (array_key_exists("optional_required_fields", $_REQUEST)) {
			foreach($request["optional_required_fields"] as $k=>$v) {
				if ($v != 0) $recs[] = sprintf("(%d,%d)", $data["id"], $v);
			}
			if (count($recs) > 0) $this->execute(sprintf("replace into member_optional_fields(member_id, optional_id) values%s", implode(", ", $recs)));
			$this->execute(sprintf("delete from member_optional_fields where member_id = %d and optional_id not in (%s)", $data["id"], implode(", ",array_merge(array(0),array_values($request["optional_required_fields"])))));
		}
		else {
			$this->execute(sprintf("delete from member_optional_fields where member_id = %d", $data["id"]));
		}
	}

    /**
     * @param $data
     * @param $request
     * @return void
     */
    public function memberFolderUpdate($data, $request) {
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function getMemberNames() {
		$m = array_key_exists("m",$_REQUEST) ? $_REQUEST["m"] : 0;
		$s = array_key_exists("s",$_REQUEST) ? $_REQUEST["s"] : "";
		$s = "%".$s."%";
		$o = new select();
		$o->addAttribute('sql',sprintf("select id, concat(company,' ',lastname,' ',firstname) as name from members where id = %d or (company like '%s' or firstname like '%s' or lastname like '%s') and enabled = 1 and deleted = 0",$m,$s,$s,$s));
		return $this->ajaxReturn(array('status'=>true,'html'=>$o->show()));
	}

    /**
     * @param $d_id
     * @return bool
     * @throws phpmailerException
     */
    function calcDriverCommission($d_id) {
		//
		//	calculate the drivers commission on a pickup or delivery
		//	main split already done, now apply the driver's % and calc fuel surcharge
		//
		$r = $this->fetchSingle(sprintf("select * from custom_delivery where id = %d",$d_id));
		$d = $this->fetchSingle(sprintf("select * from drivers where id = %d",$r["driver_id"]));
		$o = $this->fetchSingle(sprintf("select * from orders where id = %d",$r["order_id"]));
		$p = $o["custom_commissionable_amt"] * $r["percent_of_delivery"]/100 * $d["commission"]/100;
		$p += $o["custom_commissionable_amt"] * $d["fuel_surcharge"]/100;
		return $this->execute(sprintf("update custom_delivery set payment = %s where id = %d",round($p,2),$r["order_id"]));
	}

    /**
     * @return array
     */
    function orderDisplay() {
		return array(
			'form'=>file_get_contents($this->M_DIR.'forms/order.html'),
			'description'=>'KJV Parameters',
			'fields'=>array(
				'custom_reference_number'=>array('type'=>'textfield','required'=>false,'value'=>''),
				'custom_qb_order'=>array('type'=>'number','validation'=>'number','prettyName'=>'Quickbooks Order #','min'=>0,'class'=>'a-right def_field_input','disabled'=>'disabled'),
				'custom_insurance'=>array('type'=>'checkbox','value'=>'1'),
				'customs_declaration'=>array('type'=>'textarea'),
				'custom_placed_by'=>array('type'=>'textfield'),
				'custom_3rd_party_waybill'=>array('type'=>'textfield'),
				'custom_declared_value'=>array('type'=>'textfield','validation'=>'number','class'=>'def_field_input a-right'),
				'custom_email_confirmation'=>array('type'=>'textfield','validation'=>'email','required'=>false,'prettyName'=>'Delivery Confirmation Email'),
				'recurring_sequence_pickup'=>array('type'=>'number','min'=>0, 'required'=>true, 'class'=>'def_field_input a-right', 'step'=>10),
				'recurring_sequence_delivery'=>array('type'=>'number','min'=>0, 'required'=>true, 'class'=>'def_field_input a-right', 'step'=>10),
				'custom_pickup_email'=>array('type'=>'textfield','validation'=>'email','required'=>false,'prettyName'=>'Pickup Confirmation Email')
		));
	}

    /**
     * @param $parms
     * @param $obj
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function enhancedProductList($parms, $obj) {
		$this->logMessage(__FUNCTION__,sprintf("parms [%s] obj [%s]", print_r($parms,true), print_r($obj,true)),1);
		$outer = new Forms();
		$inner = new Forms();
		$outer->init($this->M_DIR.sprintf("forms/%s.html",__FUNCTION__));
		$inner->init($this->M_DIR.sprintf("forms/%sRow.html",__FUNCTION__));
		$folders = $this->fetchAll(sprintf('select * from product_folders where enabled = 1 order by left_id'));
		$opts = array();
		$ct = 0;
		$p_id = $obj->getData("product_id");
		foreach($folders as $k=>$fldr) {
			$inner->addData($fldr);
			$opts[] = sprintf("<optgroup label='%s'>",$fldr["title"]);
			$products = $this->fetchAll(sprintf("select p.* from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id order by pf.sequence", $fldr["id"]));
			foreach($products as $sk=>$prod) {
				$inner->addData($prod);
				$inner->setData("selected", $prod["id"] == $p_id ? "selected=selected":"");
				$opts[] = $inner->show();
			}
			$opts[] = "</optgroup>";
		}
		$outer->setData("rows",implode("",$opts));
		return $outer->show();
	}

    /**
     * @return string
     * @throws phpmailerException
     */
    function optgroup() {
		$this->logMessage(__FUNCTION__,sprintf("this [%s]", print_r($this,true)), 1);
		$grps = $this->fetchAll(sprintf("select * from product_folders order by left_id"));
		$menu[] = "<select name='product_id'>";
		foreach($grps as $k=>$v) {
			$menu[] = $this->subOptGroup($v);
		}
		$menu[] = "</select>";
		return implode("",$menu);
	}

    /**
     * @param $fldr
     * @return string
     * @throws phpmailerException
     */
    function subOptGroup($fldr) {
		$grp[] = sprintf("<optgroup label='%s'>", $fldr["title"]);
		$prods = $this->fetchAll(sprintf("select * from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id", $fldr["id"]));
		foreach($prods as $k=>$v) {
			$grp[] = sprintf("<option value='%d'>%s</option>", $v["id"], $v["name"]);
		}
		$grp[] = "</optgroup>";
		return implode("",$grp);
	}

}

?>