<?php

class product extends Backend {

	private $m_tree = 'product_folders';
	private $m_content = 'product';
	private $m_junction = 'product_by_folder';
	private $m_package = 'product_by_package';
	private $m_perrow = 5;

	public function __construct() {
		$this->M_DIR = 'backend/modules/product/';
		$this->M_ROOT = '/images/products/originals/';
		$this->setTemplates(
			array(
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'loadPricing'=>$this->M_DIR.'forms/loadPricing.html',
				'editPricing'=>$this->M_DIR.'forms/editPricing.html',
				'editPricingSuccess'=>$this->M_DIR.'forms/editPricingSuccess.html',
				'loadRecurring'=>$this->M_DIR.'forms/loadRecurring.html',
				'editRecurring'=>$this->M_DIR.'forms/editRecurring.html',
				'editRecurringSuccess'=>$this->M_DIR.'forms/editRecurringSuccess.html',
				'loadOptions'=>$this->M_DIR.'forms/loadOptions.html',
				'editOption'=>$this->M_DIR.'forms/editOption.html',
				'editOptionSuccess'=>$this->M_DIR.'forms/editOptionSuccess.html',
				'main'=>$this->M_DIR.'product.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'productInfo'=>$this->M_DIR.'forms/productInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'inventorySearchForm'=>$this->M_DIR.'forms/inventorySearchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'productByFolder'=>$this->M_DIR.'forms/productList.html',
				'couponByProductList'=>$this->M_DIR.'forms/couponList.html',
				'blogByProductList'=>$this->M_DIR.'forms/blogList.html',
				'assemblyRow'=>$this->M_DIR.'forms/assemblyRow.html',
				'assembledFromRow'=>$this->M_DIR.'forms/assembledFromRow.html',
				'inventoryRow'=>$this->M_DIR.'forms/inventoryRow.html',
				'inventoryFromRow'=>$this->M_DIR.'forms/inventoryFromRow.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'storeList'=>$this->M_DIR.'forms/storeList.html',
				'eventList'=>$this->M_DIR.'forms/eventList.html',
				'newsList'=>$this->M_DIR.'forms/newsList.html',
				'couponList'=>$this->M_DIR.'forms/couponsList.html',
				'couponFolderList'=>$this->M_DIR.'forms/couponFoldersList.html',
				'storeContentList'=>$this->M_DIR.'forms/storeContentList.html',
				'storeFolderList'=>$this->M_DIR.'forms/storeFoldersList.html',
				'addProduct'=>$this->M_DIR.'forms/addProduct.html',
				'editItem'=>$this->M_DIR.'forms/editItem.html',
				'inventoryAudit'=>$this->M_DIR.'forms/inventoryAudit.html',
				'inventoryAuditRow'=>$this->M_DIR.'forms/inventoryAuditRow.html',
				'showInventory'=>$this->M_DIR.'forms/showInventory.html',
				'editInventory'=>$this->M_DIR.'forms/editInventory.html',
				'editInventorySuccess'=>$this->M_DIR.'forms/editInventorySuccess.html',
				'inventoryList'=>$this->M_DIR.'forms/inventoryList.html',
				'pricingRow'=>$this->M_DIR.'forms/pricingRow.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'video'=>$this->M_DIR.'forms/video.html',
				'comments'=>$this->M_DIR.'forms/comments.html',
				'commentsRow'=>$this->M_DIR.'forms/commentsRow.html',
				'editComment'=>$this->M_DIR.'forms/editComment.html',
				'editCommentSuccess'=>$this->M_DIR.'forms/editCommentSuccess.html',
				'deleteComment'=>$this->M_DIR.'forms/deleteComment.html',
				'approval'=>$this->M_DIR.'forms/approveComment.html'
			)
		);
		$this->setFields(array(
			'deleteItem'=>array(
				'options'=>array('name'=>'deleteItem','database'=>false),
				'j_id'=>array('type'=>'tag'),
				'deleteItem'=>array('type'=>'hidden','value'=>1),
				'cancel'=>array('type'=>'radiobutton','name'=>'action','value'=>'cancel','checked'=>'checked'),
				'one'=>array('type'=>'radiobutton','name'=>'action','value'=>'one'),
				'all'=>array('type'=>'radiobutton','name'=>'action','value'=>'all')
			),
			'loadOptions'=>array(),
			'editOptionSuccess'=>array(),
			'editOption'=>array(
				'description'=>array('type'=>'textarea','class'=>'mceSimple','required'=>true),
				'teaser'=>array('type'=>'input','required'=>true),
				'price'=>array('type'=>'input','required'=>true,'validation'=>'number','class'=>'def_field_input_sml a-right','value'=>'0.00'),
				'qty_multiplier'=>array('type'=>'input','required'=>true,'validation'=>'number','class'=>'def_field_input_sml a-right','value'=>'1'),
				'qty_multiplierHidden'=>array('type'=>'hidden','name'=>'qty_multiplier','required'=>false,'validation'=>'number','value'=>'1'),
				'sale_price'=>array('type'=>'input','required'=>true,'validation'=>'number','class'=>'def_field_input_sml a-right','value'=>'0.00'),
				'shipping'=>array('type'=>'input','required'=>true,'validation'=>'number','class'=>'def_field_input_sml a-right','value'=>'0.00'),
				'editOption'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'product_id'=>array('type'=>'hidden'),
				'colors'=>array('type'=>'select','name'=>'colors','sql'=>'select id, value from code_lookups where type="color" order by sort,value','multiple'=>true,'database'=>false),
				'sizes'=>array('type'=>'select','name'=>'sizes','sql'=>'select id, value from code_lookups where type="size" order by sort,value','multiple'=>true,'database'=>false),
				'save'=>array('type'=>'submitbutton','value'=>'Save Option','database'=>false,'class'=>'def_field_submit')
			),
			'editPricing'=>array(
				'min_quantity'=>array('type'=>'input','required'=>true,'class'=>'def_field_small','validation'=>'number','name'=>'min_quantity','prettyName'=>'Minimum Quantity'),
				'max_quantity'=>array('type'=>'input','required'=>true,'class'=>'def_field_small','validation'=>'number','name'=>'max_quantity','prettyName'=>'Maximum Quantity'),
				'price'=>array('type'=>'input','required'=>true,'class'=>'def_field_small','validation'=>'number','name'=>'price','prettyName'=>'Price'),
				'sale_price'=>array('type'=>'input','required'=>false,'class'=>'def_field_small','validation'=>'number','name'=>'sale_price'),
				'shipping'=>array('type'=>'input','required'=>true,'class'=>'def_field_small','validation'=>'number','name'=>'shipping','prettyName'=>'Shipping'),
				'shipping_type'=>array('type'=>'select','required'=>true,'name'=>'shipping_type','lookup'=>'shipping_cost_type','class'=>'def_field_ddl_sml'),
				'product_id'=>array('type'=>'hidden'),
				'message'=>array('type'=>'input','required'=>false,'name'=>'message','reformatting'=>true,'class'=>'def_field_long'),
				'editPricing'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'save'=>array('type'=>'submitbutton','value'=>'Save Pricing','database'=>false,'class'=>'def_field_submit')
			),
			'inventoryList'=>array(
				'name'=>array('type'=>'tag'),
				'code'=>array('type'=>'tag'),
				'quantity'=>array('type'=>'tag'),
				'start_date'=>array('type'=>'datestamp'),
				'end_date'=>array('type'=>'datestamp'),
				'deleted'=>array('type'=>'booleanIcon')
			),
			'editInventory'=>array(
				'options'=>array('name'=>'editInventory'),
				'delete'=>array('type'=>'checkbox','value'=>1),
				'start_date'=>array('type'=>'datepicker','validation'=>'date','required'=>false,'prettyName'=>'Start Date'),
				'end_date'=>array('type'=>'datepicker','validation'=>'date','required'=>false,'prettyName'=>'End Date'),
				'comments'=>array('type'=>'input'),
				'quantity'=>array('type'=>'input','required'=>true,'validation'=>'number','prettyName'=>'Quantity'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save'),
				'editInventory'=>array('type'=>'hidden','value'=>1),
				'valid'=>array('type'=>'tag','value'=>0),
				'product_id'=>array('type'=>'hidden'),
				'options_id'=>array('type'=>'select','sql'=>'select id, teaser from product_options where product_id=%%product_id%%','onchange'=>'getOptions(this);'),
				'color'=>array('type'=>'select','sql'=>'select id, value from code_lookups where id=%%color%% order by sort,value'),
				'size'=>array('type'=>'select','sql'=>'select id, value from code_lookups where id=%%size%% order by sort,value')
			),
			'showInventory'=>array(
				'options'=>array('name'=>'showInventory'),
				'pagenum'=>array('type'=>'hidden','value'=>0)
			),
			'inventoryAudit'=>array(
				'options'=>array('name'=>'inventoryAuditForm'),
				'code'=>array('type'=>'tag'),
				'name'=>array('type'=>'tag'),
				'pagenum'=>array('type'=>'hidden','value'=>0)
			),
			'inventoryAuditRow'=>array(
				'audit_date'=>array('type'=>'datetimestamp')
			),
			'couponFolderList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag')
			),
			'couponList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'name'=>array('type'=>'tag'),
				'expires'=>array('type'=>'datestamp')
			),
			'storeFolderList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag')
			),
			'storeContentList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'name'=>array('type'=>'tag'),
				'expires'=>array('type'=>'datestamp')
			),
			'eventByProductList'=>array(
				'destEvents'=>array('type'=>'select','multiple'=>'multiple','id'=>'destEvents')
			),
			'newsByProductList'=>array(
				'destNews'=>array('type'=>'select','multiple'=>'multiple','id'=>'destNews')
			),
			'couponByProductList'=>array(
				'destCoupons'=>array('type'=>'select','multiple'=>'multiple','id'=>'destCoupons')
			),
			'storeByProductList'=>array(
				'destStores'=>array('type'=>'select','multiple'=>'multiple','id'=>'destStores')
			),
			'blogByProductList'=>array(
				'destBlogs'=>array('type'=>'select','multiple'=>'multiple','id'=>'destBlogs')
			),
			'assembledFromRow'=>array(
				'code'=>array('type'=>'tag','reformatting'=>true,'database'=>false),
				'name'=>array('type'=>'tag','reformatting'=>true,'database'=>false),
				'id'=>array('type'=>'tag')
			),
			'assemblyRow'=>array(
				'code'=>array('type'=>'tag','reformatting'=>true,'database'=>false),
				'quantity'=>array('type'=>'input','validation'=>'numeric','required'=>false,'name'=>'subproduct[quantity][]','class'=>'def_field_smalltext'),
				'sequence'=>array('type'=>'input','validation'=>'numeric','required'=>false,'name'=>'subproduct[sequence][]','class'=>'def_field_smalltext'),
				'id'=>array('type'=>'tag')
			),
			'inventoryFromRow'=>array(
				'id'=>array('type'=>'tag')
			),
			'inventoryRow'=>array(
				'delete'=>array('type'=>'checkbox','reformatting'=>true,'database'=>false,'name'=>'inventory[delete][]','value'=>1),
				'start_date'=>array('type'=>'datestamp'),
				'end_date'=>array('type'=>'datestamp'),
				'comments'=>array('type'=>'tag','reformatting'=>true),
				'quantity'=>array('type'=>'tag'),
				'id'=>array('type'=>'tag')
			),
			'productByFolder'=>array(
				'destRelatedProducts'=>array('type'=>'select','id'=>'byProductSource','multiple'=>'multiple')
				//'folderList'=>array('type'=>'ul','class'=>'draggable byProductList','id'=>'byProductSource')
			),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/product'),
				'id'=>array('type'=>'tag','database'=>false),
				'code'=>array('type'=>'input','required'=>true,'prettyName'=>'Product Code'),
				'name'=>array('type'=>'input','required'=>true,'prettyName'=>'Name'),
				'subtitle'=>array('type'=>'input','required'=>false),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i','database'=>false),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'addEndDate'),
				'featured'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'physical_product'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'description'=>array('type'=>'textarea','required'=>true,'id'=>'productBody','class'=>'mceAdvanced','prettyName'=>'Description'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag'),
				'image3'=>array('type'=>'tag'),
				'image4'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'imagesel_c'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_c'),
				'imagesel_d'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_d'),
				'sale_startdate'=>array('type'=>'datetimepicker','id'=>'sale_startdate','validation'=>'datetime','required'=>false,'AMPM'=>'AMPM','prettyName'=>'Sale Start Date'),
				'sale_enddate'=>array('type'=>'datetimepicker','id'=>'sale_enddate','validation'=>'datetime','required'=>false,'AMPM'=>'AMPM','prettyName'=>'Sale End Date'),
				'image4'=>array('type'=>'tag'),
				'on_sale_message'=>array('type'=>'input'),
				'pricing'=>array('type'=>'tag','reformatting'=>false,'database'=>false),
				'exemptions'=>array('type'=>'tag','reformatting'=>false,'database'=>false),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','reformatting'=>false,'options'=>$this->nodeSelect(0,'product_folders',2,false,false,null,' and internal_link = 0')),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'blogFolders'=>array('type'=>'select','database'=>false,'id'=>'blogFolderSelector','options'=>$this->nodeSelect(0, 'blog_folders', 2, false, false),'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'newsFolders'=>array('type'=>'select','database'=>false,'id'=>'newsFolderSelector','options'=>$this->nodeSelect(0, 'news_folders', 2, false, false),'reformatting'=>false),
				'destProductFolders'=>array('type'=>'select','database'=>false,'id'=>'relatedProductFolders','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false,null,' and internal_link = 0'),'reformatting'=>false,'multiple'=>'multiple'),
				'relatedProductFolders'=>array('type'=>'select','database'=>false,'id'=>'relatedProductFolderSelector','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false),
				'relatedDestProducts'=>array('type'=>'select','database'=>false,'id'=>'relatedDestProducts','reformatting'=>false,'multiple'=>'multiple'),
				'relatedFromDestProducts'=>array('type'=>'select','database'=>false,'id'=>'relatedFromDestProducts','reformatting'=>false,'multiple'=>'multiple'),
				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destStores'=>array('type'=>'select','database'=>false,'id'=>'destStores','reformatting'=>false,'multiple'=>'multiple'),
				'destStoreFolders'=>array('type'=>'select','database'=>false,'id'=>'destStoreFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destBlogs'=>array('type'=>'select','database'=>false,'id'=>'destBlogs','reformatting'=>false,'multiple'=>'multiple'),
				'destBlogFolders'=>array('type'=>'select','database'=>false,'id'=>'destBlogFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destGalleryFolders'=>array('type'=>'select','database'=>false,'id'=>'destGalleryFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destEvents'=>array('type'=>'select','database'=>false,'id'=>'destEvents','reformatting'=>false,'multiple'=>'multiple'),
				'destEventFolders'=>array('type'=>'select','database'=>false,'id'=>'destEventFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destNews'=>array('type'=>'select','database'=>false,'id'=>'destNews','reformatting'=>false,'multiple'=>'multiple'),
				'destNewsFolders'=>array('type'=>'select','database'=>false,'id'=>'destNewsFolders','reformatting'=>false,'multiple'=>'multiple'),
				'attachment_name'=>array('type'=>'tag'),
				'attachment_local'=>array('type'=>'input'),
				'attachment_upload'=>array('type'=>'fileupload','database'=>false),
				'attachment_title'=>array('type'=>'input'),
				'attachment_content'=>array('type'=>'tag','database'=>false),
				'attachment_protected'=>array('type'=>'checkbox','value'=>1),
				'attachment_remove'=>array('type'=>'checkbox','value'=>1,'database'=>false),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'product_folders', 2, false, false,null,' and internal_link = 0'),'reformatting'=>false,'prettyName'=>'Member Of'),
				'twitterPublish'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'facebookPublish'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'status'=>array('type'=>'select','required'=>true,'idlookup'=>'product_status'),
				'weight_units'=>array('type'=>'select','required'=>true,'idlookup'=>'weights'),
				'weight'=>array('type'=>'textfield','required'=>false,'validate'=>'number'),
				'measurement_units'=>array('type'=>'select','required'=>true,'idlookup'=>'dimensions'),
				'height'=>array('type'=>'textfield','required'=>false,'validate'=>'number'),
				'width'=>array('type'=>'textfield','required'=>false,'validate'=>'number'),
				'depth'=>array('type'=>'textfield','required'=>false,'validate'=>'number'),
				'box_size'=>array('type'=>'select','required'=>false,'sql'=>'select id, name from packaging order by name' ),
				'max_per_box'=>array('type'=>'textfield','required'=>false,'validate'=>'number'),
				'destSearch'=>array('name'=>'destSearch','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destSearch','database'=>false,'options'=>$this->nodeSelect(0, 'search_groups', 2, false, false),'reformatting'=>false,'prettyName'=>'Search Related'),
				'meta_description'=>array('name'=>'meta_description','type'=>'textarea','required'=>false),
				'social_description'=>array('name'=>'social_description','type'=>'textarea','required'=>false),
				'meta_keywords'=>array('name'=>'meta_keywords','type'=>'textarea','required'=>false),
				'browser_title'=>array('name'=>'browser_title','type'=>'textfield','required'=>false),
				'adword_conversion'=>array('type'=>'textarea','required'=>false),
				'manufacturer_id'=>array('type'=>'select','idlookup'=>'manufacturer')
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'opt_expires'=>array('type'=>'select','name'=>'opt_expires','lookup'=>'search_options'),
				'opt_code'=>array('type'=>'select','name'=>'opt_code','lookup'=>'search_string'),
				'code'=>array('type'=>'input','required'=>false),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_string'),
				'name'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datepicker','required'=>false),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'searchExpires'),
				'published'=>array('type'=>'select','lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'featured'=>array('type'=>'select','lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'folder'=>array('type'=>'select','optionslist' => array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>false),'database'=>false),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'inventorySearchForm'=>array(
				'options'=>array('action'=>'inventorySearch'),
				'opt_start_date'=>array('type'=>'select','name'=>'opt_start_date','lookup'=>'search_options'),
				'start_date'=>array('type'=>'datepicker','required'=>false),
				'opt_end_date'=>array('type'=>'select','name'=>'opt_end_date','lookup'=>'search_options'),
				'end_date'=>array('type'=>'datepicker','required'=>false,'id'=>'searchExpires'),
				'opt_code'=>array('type'=>'select','name'=>'opt_code','lookup'=>'search_string'),
				'code'=>array('type'=>'input','required'=>false),
				'inventory_date'=>array('type'=>'datepicker','required'=>false),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_string'),
				'name'=>array('type'=>'input','required'=>false),
				'opt_quantity'=>array('type'=>'select','name'=>'opt_quantity','lookup'=>'search_options'),
				'quantity'=>array('type'=>'input','required'=>false,'validation'=>'number'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'inventorySearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'folder'=>array('type'=>'select','optionslist' => array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>false),'database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'showFolderContent'=>array(
				'options'=>array('action'=>'showPageContent'),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image'=>array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'showFolderContent'=>array('type'=>'hidden','value'=>1)
			),
			'main' => array(
				'test'=>array('type'=>'tag')
			),
			'form' => array(),
			'folderProperties' => array(
				'options'=>array(
					'action'=>'/modit/product/showPageProperties',
					'method'=>'post',
					'name'=>'folderData'
				),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'showPageProperties'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'alternate_title'=>array('type'=>'textfield','required'=>false),
				'p_id'=>array('type'=>'select','required'=>true,'database'=>false,'optionslist'=>array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>true)),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'notes'=>array('type'=>'textarea','required'=>false,'class'=>'mceNoEditor'),
				'description'=>array('type'=>'textarea','required'=>false, 'id'=>'folderDescription','class'=>'mceAdvanced'),
				'teaser'=>array('type'=>'textarea','required'=>false, 'id'=>'folderTeaser','class'=>'mceSimple'),
				//'id'=>array('type'=>'hidden', 'database'=>false),
				'image'=>array('type'=>'tag'),
				'rollover_image'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'template_id'=>array('type'=>'select','required'=>false,'sql'=>'select template_id,title from templates group by title order by title'),
				'destRelatedCategories'=>array('type'=>'select','database'=>false,'id'=>'destRelatedCategories','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false,'multiple'=>'multiple'),
				'destRelatedTo'=>array('type'=>'select','database'=>false,'id'=>'destRelatedTo','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false,'multiple'=>'multiple'),
				'destRelatedProducts'=>array('type'=>'select','database'=>false,'id'=>'byProductSource','reformatting'=>false,'multiple'=>'multiple','name'=>'destRelatedProducts'),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','reformatting'=>false,'options'=>$this->nodeSelect(0,'product_folders',2,false,false)),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'blogFolders'=>array('type'=>'select','database'=>false,'id'=>'blogFolderSelector','options'=>$this->nodeSelect(0, 'blog_folders', 2, false, false),'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destStores'=>array('type'=>'select','database'=>false,'id'=>'destStores','reformatting'=>false,'multiple'=>'multiple'),
				'destStoreFolders'=>array('type'=>'select','database'=>false,'id'=>'destStoreFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destBlogs'=>array('type'=>'select','database'=>false,'id'=>'destBlogs','reformatting'=>false,'multiple'=>'multiple'),
				'destBlogFolders'=>array('type'=>'select','database'=>false,'id'=>'destBlogFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destGalleryFolders'=>array('type'=>'select','database'=>false,'id'=>'destGalleryFolders','reformatting'=>false,'multiple'=>'multiple'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'internal_link'=>array('type'=>'select','sql'=>'select id, concat(repeat("&nbsp;",(level-1)*2),title) from product_folders where internal_link = 0 order by left_id','required'=>false,'reformatting'=>false ),
				'meta_description'=>array('name'=>'meta_description','type'=>'textarea','required'=>false),
				'meta_keywords'=>array('name'=>'meta_keywords','type'=>'textarea','required'=>false),
				'browser_title'=>array('name'=>'browser_title','type'=>'textfield','required'=>false)
			),
			'showContentTree' => array(),
			'productInfo' => array(),
			'showProductContent' => array(),
			'folderInfo' => array(
				'title'=>array('type'=>'tag'),
				'alternate_title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image' => array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true)
			),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i'),
				'published'=>array('type'=>'booleanIcon'),
				'expires'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true),
				'enabled'=>array('type'=>'booleanIcon'),
				'deleted'=>array('type'=>'booleanIcon')
			),
			'loadRecurring'=>array(
				'deleted'=>array('type'=>'booleanIcon'),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'shipping_only'=>array('type'=>'booleanIcon')
			),
			'editRecurring'=>array(
				'min_amount'=>array('type'=>'input','required'=>true,'class'=>'def_field_small','validation'=>'number','name'=>'min_amount','prettyName'=>'Minimum Amount','value'=>0),
				'lookup_id'=>array('type'=>'select','required'=>true,'class'=>'def_field_ddl','name'=>'lookup_id','prettyName'=>'Recurring Code','idlookup'=>'recurringBilling'),
				'teaser'=>array('type'=>'input','required'=>true,'class'=>'def_field_textfield','name'=>'teaser','prettyName'=>'Teaser'),
				'discount_rate'=>array('type'=>'input','required'=>true,'class'=>'def_field_small','validation'=>'number','name'=>'discount_rate'),
				'shipping_only'=>array('type'=>'checkbox','required'=>false,'class'=>'def_field_small','name'=>'shipping_only','prettyName'=>'Shipping Only','value'=>1),
				'enabled'=>array('type'=>'checkbox','required'=>false,'name'=>'enabled','value'=>1),
				'product_id'=>array('type'=>'hidden'),
				'deleted'=>array('type'=>'checkbox','required'=>false,'name'=>'deleted','class'=>'def_field_small','value'=>1),
				'published'=>array('type'=>'checkbox','required'=>false,'name'=>'published','class'=>'def_field_small','value'=>1),
				'editRecurring'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'percent_or_dollar'=>array('type'=>'select','required'=>true,'options'=>array('D'=>'Dollar','P'=>'Percent')),
				'save'=>array('type'=>'submitbutton','value'=>'Save Recurring Option','database'=>false,'class'=>'def_field_submit')
			),
			'video'=>array(
				'url'=>array('type'=>'textfield','required'=>true,'name'=>'video[url]','prettyName'=>'Video URL'),
				'video_id'=>array('type'=>'textfield','required'=>true,'name'=>'video[video_id]','prettyName'=>'Video Id'),
				'height'=>array('type'=>'textfield','class'=>'def_field_small','validation'=>'number','name'=>'video[height]','prettyName'=>'Video height'),
				'width'=>array('type'=>'textfield','class'=>'def_field_small','validation'=>'number','name'=>'video[width]','prettyName'=>'Video Width'),
				'embed_code'=>array('type'=>'textarea','name'=>'video[embed_code]','prettyName'=>'Video Embed Code'),
				'title'=>array('type'=>'textfield','required'=>true,'name'=>'video[title]','prettyName'=>'Video Title'),
				'thumbnail'=>array('type'=>'textfield','required'=>false,'name'=>'video[thumbnail]','prettyName'=>'Thumbnail'),
				'fetchFromVimeo'=>array('type'=>'checkbox','value'=>1,'database'=>false),
				'media_type'=>array('type'=>'select','required'=>true,'lookup'=>'multimedia_type'),
				'v_id'=>array('type'=>'hidden','value'=>'%%owner_id%%','name'=>'video[v_id]','database'=>false),
				'video_host'=>array('type'=>'select','lookup'=>'video_hosting','required'=>true,'name'=>'video[host]','prettyName'=>'Video Hosted By')
			),
			'comments'=>array(
				'p_id'=>array('type'=>'hidden'),
				'pagenum'=>array('type'=>'hidden'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
			),
			'commentsRow'=>array(
				'created'=>array('type'=>'datetimestamp'),
				'approved'=>array('type'=>'booleanIcon')
			),
			'editComment'=>array(
				'firstname'=>array('type'=>'textfield','required'=>true),
				'lastname'=>array('type'=>'textfield','required'=>true),
				'email'=>array('type'=>'textfield','required'=>true,'validation'=>'email'),
				'approved'=>array('type'=>'checkbox','value'=>1),
				'comment'=>array('type'=>'textarea','required'=>true,'class'=>'mceSimple'),
				'rating'=>array('type'=>'select','required'=>true,'lookup'=>'product_ratings'),
				'save'=>array('type'=>'submitbutton','value'=>'Save Review','database'=>false),
				'editComment'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'c_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%id%%')
			),
			'editCommentSuccess'=>array(),
			'deleteComment'=>array()
		));
	
		parent::__construct ();
	}
	
	function __destruct() {
	
	}

	function show($injector = null) {
		$this->logMessage('show',sprintf('injector [%s]',$injector),2);
		$form = new Forms();
		$form->init($this->getTemplate('main'),array('name'=>'adminMenu'));
		$frmFields = $form->buildForm($this->getFields('main'));
		if ($injector == null || strlen($injector) == 0) {
			$injector = $this->moduleStatus(true);
		}
		$form->addTag('injector', $injector, false);
		return $form->show();
	}

	function showForm() {
		$form = new Forms();
		$form->init($this->getTemplate('form'),array('name'=>'adminMenu'));
		$frmFlds = $form->buildForm($this->getFields('form'));
		$form->getField('contenttree')->addAttribute('value',$this->buildTree($this->m_tree));
		if (count($_POST) > 0) {
			$form->addData($_POST);
			if ($form->validate()) {
				$this->addMessage('Validated');
				$tmp = array();
				foreach($_POST as $key=>$value) {
					$fld = new tag();
					$tmp[] = $fld->show(sprintf('name: %s value: [%s] post: [%s]', $key, $form->getData($key), $value));
				}
				$form->addTag('info', implode('<br/>',$tmp), false);
			}
			else {
				$this->addError('Validation failed');
			}
		}
		return $form->show();
	}

	function showContentTree() {
		$form = new Forms();
		$form->init($this->getTemplate('showContentTree'),array());
		$form->addTag('tree',$this->buildTree($this->m_tree, array(), "ajaxBuild", array(0=>"<ol>%s</ol>",1=>"<li class='collapsed'>%s%s</li>",3=>"")),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
	}

	function showPageProperties($fromMain = false) {
		$result = array();
		$return = 'true';
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree, $_REQUEST['id']))))
			$data = array('enabled'=>1,'id'=>0,'p_id'=>0,'image'=>'','rollover_image'=>'');
		else {
			//
			//	get the parent node as well
			//
			$data['p_id'] = 0;
			if ($data['level'] > 1) {
				if ($p = $this->fetchSingle(sprintf('select * from %s where level = %d and left_id < %d and right_id > %d', $this->m_tree, $data['level'] - 1, $data['left_id'], $data['right_id'])))
					$data['p_id'] = $p['id'];
			}
		}
		$form = new Forms();
		$form->init($this->getTemplate('folderProperties'),array('name'=>'folderProperties'));
		$frmFlds = $this->getFields('folderProperties');

		//
		//	load coupon folders attached to this product
		//
		//$frmFlds['destCoupons']['options'] = $this->fetchOptions(sprintf('select id as code,concat(code," - ",name) as value from coupons where deleted = 0 and enabled = 1 and published = 1 order by code'));
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'productfolder','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$tmp = $this->fetchAll(sprintf('select id, code from coupons where id in (%s)',implode(',',array_merge(array(0),$data['destCoupons']))));
		foreach($tmp as $key=>$value) {
			$frmFlds['destCoupons']['options'][$value['id']] = $value['code'];
		}

		//
		//	load coupons attached to this product
		//
		$frmFlds['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'productfolder','couponfolder','coupon_folders','',true);


		//
		//	load store folders attached to this product
		//
		//$frmFlds['destStores']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from stores where deleted = 0 and enabled = 1 and published = 1 order by code'));
		$data['destStores'] = $this->loadRelations('destStores',$this,"altStoreFormat",$data['id'],'productfolder','store','stores','and deleted = 0 and enabled = 1 and published = 1',false);
		$tmp = $this->fetchAll(sprintf('select id, name from stores where id in (%s)',implode(',',array_merge(array(0),$data['destStores']))));
		foreach($tmp as $key=>$value) {
			$frmFlds['destStores']['options'][$value['id']] = $value['name'];
		}

		//
		//	load store folders attached to this product
		//
		$frmFlds['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'productfolder','storefolder','store_folders','',false);

		$data['destRelatedCategories'] = $this->loadRelations('destRelatedCategories',$this,"altProductFormat",$data['id'],'productfolder','product_category','product_folders','',true);
		$data['destRelatedTo'] = $this->loadRelations('destRelatedTo',$this,"altProductFormat",$data['id'],'product_category','productfolder','product_folders','',false);

		$data['destRelatedProducts'] = $this->loadRelations('destRelatedProducts',$this,"altProductFormat",$data['id'],'productfolder','product','product','',true);
		$tmp = $this->fetchAll(sprintf('select id, code from product where id in (%s)',implode(',',array_merge(array(0),$data['destRelatedProducts']))));
		foreach($tmp as $key=>$value) {
			$frmFlds['destRelatedProducts']['options'][$value['id']] = $value['code'];
		}
		$this->logMessage('showPageProperties',sprintf('destRelatedProducts [%s] field [%s] tmp [%s]',print_r($data,true),print_r($frmFlds['destRelatedProducts'],true),print_r($tmp,true)),1);

		//
		//	load store folders attached to this product
		//
		$data['destBlogs'] = $this->loadRelations('destBlogs',$this,"altBlogFormat",$data['id'],'productfolder','blog','blog','and deleted = 0 and enabled = 1 and published = 1',false);
		$tmp = $this->fetchAll(sprintf('select id, title from blog where id in (%s)',implode(',',array_merge(array(0),$data['destBlogs']))));
		foreach($tmp as $key=>$value) {
			$frmFlds['destBlogs']['options'][$value['id']] = $value['title'];
		}

		//
		//	load blog folders attached to this product
		//
		$frmFlds['destBlogFolders']['options'] = $this->nodeSelect(0,'blog_folders',2,false);
		$data['destBlogFolders'] = $this->loadRelations('destBlogFolders',$this,"altBlogFormat",$data['id'],'productfolder','blogfolder','blog_folders','',false);

		$frmFlds['destGalleryFolders']['options'] = $this->nodeSelect(0,'gallery_folders',2,false);
		$data['destGalleryFolders'] = $this->loadRelations('destGalleryFolders',$this,"altGalleryFormat",$data['id'],'productfolder','galleryfolder','gallery_folders','',true);

		$frmFlds = $form->buildForm($frmFlds);
		$data['imagesel_a'] = $data['image'];
		$data['imagesel_b'] = $data['rollover_image'];
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('showPageProperties',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image'];
			$_POST['imagesel_b'] = $_POST['rollover_image'];
			$form->addData($_POST);
			$valid = $form->validate();
			if ($valid) {
				if (array_key_exists('options',$frmFlds)) unset($frmFlds['options']);
				$values = array();
				$flds = array();
				if ($data['id'] == 0) {
					$mptt = new mptt($this->m_tree);
					$data['id'] = $mptt->add($_POST['p_id'],999,array('title'=>'to be replaced'));
				} 
				else {
					//
					//	did we move the parent folder?
					//
					if ($data['level'] > 1)
						$parent = $this->fetchSingle(sprintf('select * from %s where level = %d and left_id < %d and right_id > %d', $this->m_tree, $data['level'] - 1, $data['left_id'], $data['right_id']));
					else $parent['id'] = 0;
					if ($_POST['p_id'] != $parent['id']) {
						$this->logMessage('showPageProperties', sprintf('moving [%d] to [%d] posted[%d]',$data['id'],$parent['id'], $_POST['p_id']), 1);
						$mptt = new mptt($this->m_tree);
						$mptt->move($data['id'], $_POST['p_id']);
					}
				}
				foreach($frmFlds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $form->getData($fld['name']);
						if ($data['id'] > 0)
							$flds[] = sprintf('%s = ?',$fld['name']);
						else
							$flds[] = $fld['name'];
					}
				}
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree,implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				if ($status = $stmt->execute()) {
					$status = $status && $this->updateRelations('destCoupons',$data['id'],'productfolder','coupon','coupons',true,true);
					$status = $status && $this->updateRelations('destCouponFolders',$data['id'],'productfolder','couponfolder','coupon_folders',true,false);
					$status = $status && $this->updateRelations('destStores',$data['id'],'productfolder','store','stores',false,true);
					$status = $status && $this->updateRelations('destStoreFolders',$data['id'],'productfolder','storefolder','store_folders',false,false);
					$status = $status && $this->updateRelations('destRelatedCategories',$data['id'],'productfolder','product_category','product_folders',true,false);
					$status = $status && $this->updateRelations('destRelatedTo',$data['id'],'product_category','productfolder','product_folders',false,false);
					$status = $status && $this->updateRelations('destRelatedProducts',$data['id'],'productfolder','product','product',true,false);

					$status = $status && $this->updateRelations('destBlogs',$data['id'],'productfolder','blog','blog',false,true);
					$status = $status && $this->updateRelations('destBlogFolders',$data['id'],'productfolder','blogfolder','blog_folders',false,false);
					$status = $status && $this->updateRelations('destGalleryFolders',$data['id'],'productfolder','galleryfolder','gallery_folders',true,false);

				}
				if ($status) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/product?p_id='.$data['id']
						));
					}
				} else {
					$this->addError('Error occurred');
					$form->addTag('errorMessage',$this->showErrors(),false);
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax error return', 3);
						return $this->ajaxReturn(array(
								'status'=>'false',
								'html'=>$form->show()
						));
					}
				}
				$form->addTag('errorMessage','Record added succesfully');
			}
			else {
				$return = 'false';
				$form->addTag('errorMessage','Form validation failed');
			}
		}
	
		if ($this->isAjax())
			return $this->ajaxReturn(array(
					'status'=>$return,
					'html'=>$form->show()
			));
		elseif ($fromMain)
		return $form->show();
		else
			$this->show($form->show());
	}

	function ajaxBuild($data, $table, $wrappers, $submenu) {
		switch($table) {
			case $this->m_tree:
				$value = new tag(false);
				$mptt = new mptt($table);
				$children = $mptt->fetchChildren($data['id']);
				if (count($_REQUEST) > 0 && array_key_exists('p_id',$_REQUEST)) {
					$expanded = $_REQUEST['p_id'] == $data['id'] ?  'active' : '';
				}
				else $expanded='';
				if (count($submenu) > 0) {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer"><a href="#" class="toggler" onclick="toggle(this);return false;">+</a></div><a href="#" id="li_%d" class="%s icon_%s info">%s</a></div>',$data['id'], $expanded, 
						$data['internal_link'] > 0 ? 'internallink' : 'folder',
						htmlspecialchars($data['title'])),'submenu'=>$submenu);
				}
				else {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer">&nbsp;</div><a href="#" id="li_%d" class="%s icon_%s info">%s</a></div>',$data['id'], $expanded, 
						$data['internal_link'] > 0 ? 'internallink' : 'folder',
						htmlspecialchars($data['title'])),'submenu'=>array());
				}
				break;
			default:
				$value = new tag(false);
				$mptt = new mptt($table);
				$children = $mptt->fetchChildren($data['id']);
				$expanded='';
				if (count($submenu) > 0) {
					$return = array('value'=>sprintf('<div class="wrapper"><a href="#" class="toggler" onclick="toggle(this);return false;">+</a>&nbsp;<a href="#" id="li_%d" class="%s icon_folder info">%s</a></div>',$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>$submenu);
				}
				else {
					$return = array('value'=>sprintf('<div class="wrapper"><a href="#" id="%s_li_%d" class="%s icon_folder info">%s</a></div>',$table,$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>array());
				}
				break;
		}
		return $return;
	}

	function getFolderInfo($fromMain = false) {
		if (array_key_exists('p_id',$_REQUEST)) {
			if ($data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$_REQUEST['p_id']))) {
				$form = new Forms();
				$data['notes'] = nl2br($data['notes']);
				$template = 'folderInfo';
				$form->init($this->getTemplate($template), array());
				$frmFields = $form->buildForm($this->getFields($template));
				$form->addData($data);
				if ($this->isAjax())
					return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
				elseif ($fromMain)
				return $form->show();
				else
					return $this->show($form->show());
			}
		}
	}

	function showPageContent($fromMain = false) {
		$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
		$form = new Forms();
		$perPage = $this->m_perrow;
		if ($p_id > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$p_id))) {
			if (strlen($data['alternate_title']) > 0) $data['connector'] = '&nbsp;-&nbsp;';
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			if (array_key_exists('pager',$_REQUEST)) 
				$perPage = $_REQUEST['pager'];
			else {
				$tmp = $this->checkArray("formData:productSearchForm:pager",$_SESSION);
				if ($tmp > 0) 
					$perPage = $tmp;
				else
				$perPage = $this->m_perrow;
			}
			$form->setData('pager',$perPage);
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.product_id from %s f where f.folder_id = %d) and n.deleted = 0', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
				array('url'=>'/modit/ajax/showPageContent/product','destination'=>'middleContent'));
			$start = ($pageNum-1)*$perPage;
			$sortby = 'sequence';
			$sortorder = 'asc';
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST)) {
				$sortby = $_POST['sortby'];
				$sortorder = $_POST['sortorder'];
				$form->addData($_POST);
			}
			$sql = sprintf('select a.*, f.id as j_id from %s a left join %s f on a.id = f.product_id where a.deleted = 0 and f.folder_id = %d order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$products = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($products)), 2);
			$articles = array();
			foreach($products as $product) {
				$frm = new Forms();
				$frm->init($this->getTemplate('articleList'),array());
				$tmp = $frm->buildForm($this->getFields('articleList'));
				$frm->addData($product);
				$articles[] = $frm->show();
			}
			$form->addTag('articles',implode('',$articles),false);
			$form->addTag('pagination',$pagination,false);
			$form->addData($data);

			//
			//	get any applicable coupons
			//
			$sql = sprintf('select * from coupons c, relations r where c.deleted = 0 and r.owner_id = %d and r.owner_type = "productfolder" and c.id = r.related_id and r.related_type="coupon"',$data['id']);
			$coupons = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('coupons [%s], records [%d]',$sql, count($coupons)), 2);
			$output = array();
			foreach($coupons as $coupon) {
				$frm = new Forms();
				$frm->init($this->getTemplate('couponList'),array());
				$tmp = $frm->buildForm($this->getFields('couponList'));
				$frm->addData($coupon);
				$output[] = $frm->show();
			}
			$form->addTag('coupons',implode('',$output),false);

			$sql = sprintf('select * from coupon_folders c, relations r where r.owner_id = %d and r.owner_type = "productfolder" and c.id = r.related_id and r.related_type="couponfolder"',$data['id']);
			$coupons = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('coupon folders [%s], records [%d]',$sql, count($coupons)), 2);
			$output = array();
			foreach($coupons as $coupon) {
				$frm = new Forms();
				$frm->init($this->getTemplate('couponFolderList'),array());
				$tmp = $frm->buildForm($this->getFields('couponFolderList'));
				$frm->addData($coupon);
				$output[] = $frm->show();
			}
			$form->addTag('couponFolders',implode('',$output),false);


			//
			//	get any applicable stores
			//
			$sql = sprintf('select * from stores c, relations r where c.deleted = 0 and r.related_id = %d and r.related_type = "productfolder" and c.id = r.owner_id and r.owner_type="store"',$data['id']);
			$stores = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('stores [%s], records [%d]',$sql, count($stores)), 2);
			$output = array();
			foreach($stores as $store) {
				$frm = new Forms();
				$frm->init($this->getTemplate('storeContentList'),array());
				$tmp = $frm->buildForm($this->getFields('storeContentList'));
				$frm->addData($store);
				$output[] = $frm->show();
			}
			$form->addTag('stores',implode('',$output),false);

			$sql = sprintf('select * from store_folders c, relations r where r.related_id = %d and r.related_type = "productfolder" and c.id = r.owner_id and r.owner_type="storefolder"',$data['id']);
			$stores = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('store folders [%s], records [%d]',$sql, count($stores)), 2);
			$output = array();
			foreach($stores as $store) {
				$frm = new Forms();
				$frm->init($this->getTemplate('storeFolderList'),array());
				$tmp = $frm->buildForm($this->getFields('storeFolderList'));
				$frm->addData($store);
				$output[] = $frm->show();
			}
			$form->addTag('storeFolders',implode('',$output),false);

		}
		$form->addTag('heading',$this->getHeader(),false);
		if (array_key_exists('formData',$_SESSION) && array_key_exists('productSearchForm', $_SESSION['formData']))
			$_SESSION['formData']['productSearchForm']['pager'] = $perPage;
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp)); 
		}
		elseif ($fromMain)
		return $form->show();
		else
			return $this->show($form->show());
	}

	function showSearchForm($fromMain = false,$msg = '') {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'showSearchForm','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('productSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['productSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc');
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				if (strlen($form->getData("quicksearch")) > 0) {
					$_SESSION['formData']['productSearchForm'] = array('showSearchForm'=>1,'opt_quicksearch'=>'like','quicksearch'=>$form->getData("quicksearch"),'pager'=>$form->getData("pager"));
				}
				else
					$_SESSION['formData']['productSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && $_POST['opt_quicksearch'] != null && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' code %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' name %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' description %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch = array(sprintf('(%s)',implode(' or ',$tmp)),'deleted = 0');
								break 2;
							}
							break;
						case 'name':
						case 'code':
							if (array_key_exists("opt_$key",$_POST) && $_POST["opt_$key"] != null && $value = $form->getData($key)) {
								if ($_POST["opt_$key"] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' %s %s "%s"',$key,$_POST["opt_$key"],$this->escape_string($value));
							}
							break;
						case 'created':
						case 'expires':
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != null && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like') {
									$this->addError('Like is not supported for dates');
								}
								else
									$srch[] = sprintf(' %s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'folder':
							if (($value = $form->getData($key)) > 0) {
								$srch[] = sprintf(' n.id in (select product_id from %s where folder_id = %d) ', $this->m_junction, $value);
							}
							break;
						case 'featured':
						case 'enabled':
						case 'published':
						case 'deleted':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' %s = %s',$key,$this->escape_string($value));
							break;
						default:
							break;
					}
				}
				if (count($srch) > 0) {
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					if (array_key_exists('pager',$_REQUEST)) 
						$perPage = $_REQUEST['pager'];
					else {
						$tmp = $this->checkArray("formData:productSearchForm:pager",$_SESSION);
						if ($tmp > 0) 
							$perPage = $tmp;
						else
						$perPage = $this->m_perrow;
					}
					$form->setData('pager',$perPage);
					$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
							'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
							array('url'=>'/modit/ajax/showSearchForm/product','destination'=>'middleContent')
						);
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'desc';
					$sortby = 'created';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						$sortorder = $_POST['sortorder'];
					}
					$sql = sprintf('select n.*, 0 as j_id from %s n where %s order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					foreach($recs as $article) {
						$frm = new Forms();
						$frm->init($this->getTemplate('articleList'),array());
						$tmp = $frm->buildForm($this->getFields('articleList'));
						$frm->addData($article);
						$articles[] = $frm->show();
					}
					$form->addTag('articles',implode('',$articles),false);
					$form->addTag('pagination',$pagination,false);
					$form->addTag('statusMessage',sprintf('We found %d record%s matching the criteria',$count,$count != 1 ? 's' : ''));
				}
			}
		}
		$form->addTag('heading',$this->getHeader(),false);
		if (strlen($msg) > 0) $form->addTag('statusMessage',$msg,false);
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function showSearchResults($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchResults'),array('name'=>'showSearchResults','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchResults'));
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
		
	}

	function addContent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addContent'),array('name'=>'addContent'));
		$frmFields = $this->getFields('addContent');
		if (!(array_key_exists('p_id',$_REQUEST) && $_REQUEST['p_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['p_id'])))) {
			$data = array('id'=>0,'published'=>true,'enabled'=>true,'physical_product'=>true,'tax_exemptions'=>'||','image1'=>'','image2'=>'','image3'=>'','image4'=>'','attachment_protected'=>0,'custom_availability'=>0); 
		}

		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where product_id = %d', $this->m_junction, $data['id']));
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		if ((count($_REQUEST) > 0 && array_key_exists('destSearch',$_REQUEST)) || $data['id'] > 0) {
			$srch = array();
			if (array_key_exists('destSearch',$_REQUEST)) {
				$srch = $_REQUEST['destSearch'];
				if (!is_array($srch)) $srch = array($srch);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from product_by_search_group where product_id = %d', $data['id']));
				$srch = array_merge($srch,$tmp);
			}
			if (count($srch) > 0) {
				$data['destSearch'] = $srch;
			}
			$this->logMessage(__FUNCTION__,sprintf('search folders [%s]',print_r($srch,true)),1);
		}

		$frmFields['destProducts'] = array('type'=>'tag','class'=>'draggable byProductList','id'=>'toProductList','options'=>array(),'database'=>false,'reformatting'=>false);
		if (count($_REQUEST) > 0 && array_key_exists('destProducts',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destProducts',$_REQUEST)) {
				$ids = $_REQUEST['destProducts'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select id from product_by_package where product_id = %d order by sequence',$data['id']));
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$ids = implode(',',$ids);
				$source = $this->fetchAll(sprintf('select k.* from product_by_package k where k.id in (%s) order by sequence', $ids));
				$rows = array();
				foreach($source as $key=>$assembly) {
					$rows[] = $this->assemblyRow($assembly['id'],true);
				}
				$data['destProducts'] = implode('',$rows);
			}
		}

		//
		//	grab the products this is assembled into
		//
		$frmFields['assembledInto'] = array('type'=>'tag','database'=>false,'reformatting'=>false);
		$source = $this->fetchAll(sprintf('select k.* from product_by_package k where k.subproduct_id = %d', $data['id']));
		$rows = array();
		foreach($source as $key=>$assembly) {
			$rows[] = $this->assemblyRow($assembly['id'],false);
		}
		$data['assembledInto'] = implode('',$rows);

		//
		//	load coupon folders attached to this folder
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'product','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destCoupons']['options'] = $this->fetchOptions(sprintf('select id, code from coupons where id in (%s)',implode(',',array_merge(array(0),$data['destCoupons']))));

		//
		//	load coupons attached to this folder
		//
		$frmFields['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'product','couponfolder','coupon_folders','',true);
		
		//
		//	load event folders attached to this folder
		//
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'product','event','events','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from events where id in (%s)',implode(',',array_merge(array(0),$data['destEvents']))));

		$frmFields['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'product','eventfolder','members_folders','',true);

		$data['destStores'] = $this->loadRelations('destStores',$this,"altStoreFormat",$data['id'],'product','store','stores','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destStores']['options'] = $this->fetchOptions(sprintf('select id, name from stores where id in (%s)',implode(',',array_merge(array(0),$data['destStores']))));

		$frmFields['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'product','storefolder','store_folders','',true);

		$frmFields['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'product','product_category','product_folders','',true);

		$data['relatedDestProducts'] = $this->loadRelations('relatedDestProducts',$this,"altProductFormat",$data['id'],'product','product','product','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id, concat(code," - ",name) from product where id in (%s)',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		$data['relatedFromDestProducts'] = $this->loadRelations('relatedFromDestProducts',$this,"altProductFormat",$data['id'],'product','product','product','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['relatedFromDestProducts']['options'] = $this->fetchOptions(sprintf('select id, concat(code," - ",name) from product where id in (%s)',implode(',',array_merge(array(0),$data['relatedFromDestProducts']))));

		$data['destNews'] = $this->loadRelations('destNews',$this,"altNewsFormat",$data['id'],'product','news','news','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destNews']['options'] = $this->fetchOptions(sprintf('select id as code,title as value from news where id in (%s)',implode(',',array_merge(array(0),$data['destNews']))));
		$frmFields['destNewsFolders']['options'] = $this->nodeSelect(0,'news_folders',2,false);
		$data['destNewsFolders'] = $this->loadRelations('destNewsFolders',$this,"altNewsFormat",$data['id'],'newsfolder','product','news_folders','',true);

		//
		//	load blog & folders attached to this product
		//
		$data['destBlogs'] = $this->loadRelations('destBlogs',$this,"altBlogFormat",$data['id'],'product','blog','blog','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destBlogs']['options'] = $this->fetchOptions(sprintf('select id as code,title as value from blog where id in (%s)',implode(',',array_merge(array(0),$data['destBlogs']))));

		$frmFields['destBlogFolders']['options'] = $this->nodeSelect(0,'blog_folders',2,false);
		$data['destBlogFolders'] = $this->loadRelations('destBlogFolders',$this,"altBlogFormat",$data['id'],'productfolder','blogfolder','blog_folders','',false);
		$this->logMessage(__FUNCTION__,sprintf('destBlogFolders [%s] [%s] frmFields [%s]',print_r($frmFields['destBlogFolders'],true),print_r($data['destBlogFolders'],true),print_r($frmFields,true)),1);

		$frmFields['destGalleryFolders']['options'] = $this->nodeSelect(0,'gallery_folders',2,false);
		$data['destGalleryFolders'] = $this->loadRelations('destGalleryFolders',$this,"altGalleryFormat",$data['id'],'product','galleryfolder','gallery_folders','',true);
		$this->logMessage(__FUNCTION__,sprintf('destGalleryFolders [%s] frmFields [%s]',print_r($data['destGalleryFolders'],true),print_r($frmFields,true)),1);

		//
		//	build the tax exemptions
		//
		$exemptions = array();
		$taxes = $this->fetchAll('select distinct name from taxes where deleted = 0 order by name');
		foreach($taxes as $tax) {
			$tag = new checkbox();
			$tag->addAttributes(array('value'=>$tax['name'],'name'=>'exemptions[]'));
			if (strpos($data['tax_exemptions'],'|'.$tax['name'].'|') !== false ||
				(array_key_exists('exemptions',$_POST) && array_key_exists($tax['name'],$_POST['exemptions'])))
				$tag->addAttribute('checked','checked');
			$exemptions[] = $tag->show().'&nbsp;'.$tax['name'];
		}
		$data['exemptions'] = implode('&nbsp;',$exemptions);

		$customFields = new custom();
		if (method_exists($customFields,'productDisplay')) {
			$custom = $customFields->productDisplay($data,$form);
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}

		$frmFields = $form->buildForm($frmFields);
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$data['imagesel_c'] = $data['image3'];
		$data['imagesel_d'] = $data['image4'];
		$form->addTag('options',$this->loadOptions($data['id']),false);
		$form->addTag('pricing',$this->loadPricing($data['id']),false);
		$form->addTag('recurring',$this->loadRecurring($data['id']),false);
		$form->addData($data);
		
		$form->addTag('video',$this->getVideo($data['id'],false),false);

		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$_POST['imagesel_c'] = $_POST['image3'];
			$_POST['imagesel_d'] = $_POST['image4'];
			$form->addData($_POST);
			$status = $form->validate();
			if (array_key_exists('video',$_POST) && count($_POST['video']) > 0) {
				$status = $status && $this->validateVideo($form);
				$form->addTag('video',$this->getVideo($data['id'],true),false);
			}
			$blob = false;
			if ($status) {
				//
				//	new, or protected updated form non-protected
				//
				if ($form->getData('attachment_protected') == 1 && $data['attachment_protected'] == 0) {
					if (count($_FILES) == 0) {
						$status = false;
						$form->addError('Protected Content was requested, but no file attachment was given');
					}
					else {
						$form->setData('attachment_name',$_FILES['attachment_upload']['name']);
						$form->setData('attachment_mime_type',$_FILES['attachment_upload']['type']);
						$form->setData('attachment_content',file_get_contents($_FILES['attachment_upload']['tmp_name']));
						$blob = true;
					}
				}
				//
				//	protected changed to non-protected
				//
				if ($form->getData('attachment_protected') == 0 && $data['attachment_protected'] == 1) {
					$form->setData('attachment_name','');
					$form->setData('attachment_mime_type','');
					$form->setData('attachment_content','');
					$blob = true;
				}
				//
				//	protected still protected - a new file uploaded?
				//
				if ($form->getData('attachment_protected') == 1 && $data['attachment_protected'] == 1) {
					if (count($_FILES) != 0) {
						$form->setData('attachment_name',$_FILES['attachment_upload']['name']);
						$form->setData('attachment_mime_type',$_FILES['attachment_upload']['type']);
						$form->setData('attachment_content',file_get_contents($_FILES['attachment_upload']['tmp_name']));
						$blob = true;
					}
				}
			}
			if ($status) {
				$id = $_POST['p_id'];
				unset($frmFields['p_id']);
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $form->getData($fld['name']);	//$_REQUEST[$fld['name']];
						$flds[$fld['name']] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
					}
				}
				if (array_key_exists('exemptions',$_POST)) {
					$exemptions = '|'.implode('|',$_POST['exemptions']).'|';
				} else $exemptions = '||';
				$flds['tax_exemptions'] = $exemptions;
				if ($id == 0) {
					$flds['created'] = date('c');
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)', $this->m_content, implode(', ',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
					$this->addMessage('adding record');
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_content, implode('=?, ',array_keys($flds)).'=?',$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
					$this->addMessage('updating record');
				}
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					if ($id == 0) {
						$id = $this->insertId();
						$form->setData('id',$id);
					}
					if ($blob) {
						$stmt = $this->prepare(sprintf('update product set attachment_content = ?, attachment_mime_type = ? where id = %d',$id));
						$stmt->bindParams(array('bs',NULL,$form->getData('attachment_mime_type')));
						$stmt->getStatement()->send_long_data(0,$form->getData('attachment_content'));
						$status = $status && $stmt->execute();
					}
					$destFolders = $_POST['destFolders'];
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where product_id = %d and folder_id not in (%s)', $this->m_junction, $id,implode(',',$destFolders)));
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where product_id = %d and folder_id in (%s))',
						$this->m_tree,implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders)));
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(product_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}

					$destSearch = array_key_exists('destSearch',$_POST) ? $_POST['destSearch'] : array(0);
					if (!is_array($destSearch)) $destSearch = array($destSearch);
					$this->execute(sprintf('delete from product_by_search_group where product_id = %d and folder_id not in (%s)', $id, implode(',',$destSearch)));
					$new = $this->fetchScalarAll(sprintf('select id from search_groups where id in (%s) and id not in (select folder_id from product_by_search_group where product_id = %d and folder_id in (%s))',
						implode(',',$destSearch),$id,implode(',',$destSearch)));
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement('insert into product_by_search_group(product_id,folder_id) values(?,?)');
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}

					if ($status) $status = $status && $this->updateRelations('destCoupons',$data['id'],'product','coupon','coupons',true,true);
					if ($status) $status = $status && $this->updateRelations('destCouponFolders',$data['id'],'product','couponfolder','coupon_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destEvents',$data['id'],'product','event','events',true,true);
					if ($status) $status = $status && $this->updateRelations('destEventFolders',$data['id'],'product','eventfolder','members_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destStores',$data['id'],'product','store','stores',true,true);
					if ($status) $status = $status && $this->updateRelations('destStoreFolders',$data['id'],'product','storefolder','store_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destProductFolders',$data['id'],'product','product_category','product_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('relatedDestProducts',$data['id'],'product','product','product',true,true);
					if ($status) $status = $status && $this->updateRelations('relatedFromDestProducts',$data['id'],'product','product','product',false,true);
					if ($status) $status = $status && $this->updateRelations('destNews',$data['id'],'product','news','news',false,true);
					if ($status) $status = $status && $this->updateRelations('destNewsFolders',$data['id'],'product','newsfolder','news_folders',false,false);
					if ($status) $status = $status && $this->updateRelations('destBlogs',$data['id'],'product','blog','blog',false,true);
					if ($status) $status = $status && $this->updateRelations('destBlogFolders',$data['id'],'product','blogfolder','blog_folders',false,false);
					if ($status) $status = $status && $this->updateRelations('destGalleryFolders',$data['id'],'product','galleryfolder','gallery_folders',true,false);
					//if ($status) $status = $status && $this->updateRelations('destNewsFolders',$data['id'],'product','newsfolder','news_folders',false,false);
					$status = $status && $this->saveVideo($id);

					//
					//	handle the kit pieces
					//
					if (array_key_exists('subproduct',$_POST) && $status) {
						if (array_key_exists('delete',$_POST['subproduct'])) {
							$deletes = $_POST['subproduct']['delete'];
							$this->logMessage('addContent',sprintf('delete array [%s]',print_r($deletes,true)),1);
							foreach($deletes as $key=>$id) {
								$this->logMessage('addContent',sprintf('deleting kit #%d',$id),1);
								$status = $status && $this->execute(sprintf('delete from product_by_package where id = %d',$id));
							}
						}
						$idx = 0;
						foreach($_POST['subproduct']['id'] as $key=>$a_id) {
							$idx += 10;
							if ($a_id > 0) {
								$obj = new preparedStatement(sprintf('update product_by_package set quantity = ?, subproduct_id = ?, sequence = ? where id = ?'));
								$obj->bindParams(array('dddd',$_POST['subproduct']['quantity'][$key],
										$_POST['subproduct']['subproduct_id'][$key],$idx,$a_id));
								$status = $status && $obj->execute();
							}
							else {
								$obj = new preparedStatement(sprintf('insert into product_by_package(product_id,quantity,subproduct_id,sequence) values(?,?,?,?)'));
								$obj->bindParams(array('dddd',$id,$_POST['subproduct']['quantity'][$key],
										$_POST['subproduct']['subproduct_id'][$key],$idx));
								$status = $status && $obj->execute();
							}
						}
					}
					if (array_key_exists('assembledFrom',$_POST) && $status) {
						if (array_key_exists('delete',$_POST['assembledFrom'])) {
							$deletes = $_POST['assembledFrom']['delete'];
							foreach($deletes as $key=>$id) {
								$this->logMessage('addContent',sprintf('deleting kit #%d',$id),1);
								$status = $status && $this->execute(sprintf('delete from product_by_package where id = %d',$id));
							}
						}
					}
					$this->buildAltSizes($form->getData('image1'));
					$this->buildAltSizes($form->getData('image2'));
					$this->buildAltSizes($form->getData('image3'));
					$this->buildAltSizes($form->getData('image4'));
					//
					//	handle the inventory - to be moved to inventory module
					//
					if (array_key_exists('inventory',$_POST) && $status) {
						if (array_key_exists('delete',$_POST['inventory'])) {
							foreach($_POST['inventory']['delete'] as $key=>$value) {
								$this->logMessage('addContent',sprintf('deleting inventory id [%d] quantity [%d]',$rec['id'],$rec['quantity']),3);
								$rec = $this->fetchSingle(sprintf('select * from product_inventory where id=%d',$value));
								$status = $status && Inventory::auditInventory($value, -$rec['quantity'], 0, 'Inventory Deleted');
								unset($_POST['inventory']['id'][$key]);	// stop edit from being applied
							}
						}
						if (array_key_exists('id',$_POST['inventory'])) {
							foreach($_POST['inventory']['id'] as $key=>$value) {
								$this->logMessage('addContent',sprintf('updating inventory id [%d] quantity [%d]',$value,$_POST['inventory']['quantity'][$key]),3);
								$fields = array('quantity'=>$_POST['inventory']['quantity'][$key],
									'start_date'=>$_POST['inventory']['start_date'][$key],
									'end_date'=>$_POST['inventory']['end_date'][$key],
									'comments'=>$_POST['inventory']['comments'][$key],
									'product_id'=>$id);
								if ($value > 0) {
									$rec = $this->fetchSingle(sprintf('select * from product_inventory where id=%d',$value));
									$where = array();
									foreach($fields as $fld=>$tmp) {
										$where[] = sprintf('%s = ?',$fld);
									}
									$obj = new preparedStatement(sprintf('update product_inventory set %s where id = %d',implode(',',$where),$value));
									$obj->bindParams(array_merge(array(str_repeat('s',count($fields))),array_values($fields)));
									$status = $status && $obj->execute();
								}
								else {
									$rec = array('quantity'=>0);
									if ($fields['quantity'] <= 0) {
										$this->logMessage('addContent','ignoring 0 qty new record',1);
										break;
									}
									$obj = new preparedStatement(sprintf('insert into product_inventory(%s) values(%s)',implode(',',array_keys($fields)),'?'.str_repeat(',?',count($fields)-1)));
									$obj->bindParams(array_merge(array(str_repeat('s',count($fields))),array_values($fields)));
									$status = $status && $obj->execute();
									$value = $this->insertId();
								}
								if ($fields['quantity']-$rec['quantity'] != 0) {
									$status = $status && Inventory::auditInventory($value, $fields['quantity']-$rec['quantity'], 0, sprintf('Inventory Updated [%s]',$fields['comments']));
								}
							}
						}
					}
					if (method_exists($customFields,'productUpdate')) {
						$flds["id"] = $form->getData("id");
						$status &= $customFields->productUpdate($flds,$_REQUEST);
					}
					if ($status) {
						$this->commitTransaction();
						if ($form->getData('twitterPublish') != 0) {
							$data = $form->getAllData();
							if ($data['published'] == 0 || $data['enabled'] == 0) {
								$this->addError('Cannot tweet an unpublished or disabled item');
								$status = false;
							}
							else {
								if (!$status = $this->twitterPost($data['name'], $data['teaser'],sprintf('http://%s%s',HOSTNAME,$this->getUrl('product',$id,$data)),$data))
									$this->addError('Error posting to Twitter');
							}
						}
						if ($form->getData('facebookPublish') != 0) {
							$data = $form->getAllData();
							if ($data['published'] == 0 || $data['enabled'] == 0) {
								$this->addError('Cannot post an unpublished or disabled item to Facebook');
								$status = false;
							}
							else {
								if (!$status = $this->facebookPost($data['name'], $data['teaser'],sprintf('http://%s%s',HOSTNAME,$this->getUrl('product',$id,$data)),$data))
									$this->addError('Error posting to Facebook');
							}
						}
						if ($status) {
							return $this->ajaxReturn(array(
								'status' => 'true',
								'url' => sprintf('/modit/product?p_id=%d',$destFolders[0])
							));
						}
					}
					else {
						$this->rollbackTransaction();
						$this->addError('An Error occurred');
					}
				} else {
					$this->rollbackTransaction();
					$this->addError('An Error occurred');
				}
			}
			else {
				$this->addError('Form Validation Failed');
			}
			$form->addTag('errorMessage',$this->showMessages(),false);
		}
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array(
				'status' => $status,
				'html' => $tmp
			));
		}
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function moveArticle() {
		$src = 0;
		$dest = 0;
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0 || !array_key_exists('type',$_REQUEST)) {
				$this->addError('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			if ($folder = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$dest))) {
				$curr = $this->fetchScalar(sprintf('select product_id from %s where id = %d',$this->m_junction,$src));
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$this->logMessage('moveArticle', sprintf('moving product %d to folder %d',$src,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingle(sprintf('select * from %s where product_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
							$obj = new preparedStatement(sprintf('insert into %s(product_id,folder_id) values(?,?)',$this->m_junction));
							$obj->bindParams(array('dd',$curr,$dest));
							$status = $obj->execute();
						}
					}
					if ($status)
						$this->commitTransaction();
					else
						$this->rollbackTransaction();
				}
				else {
					//
					//	add it - if it doesn't already exist
					//
					$curr = $src;
					$this->logMessage('moveArticle', sprintf('cloning product %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingle(sprintf('select * from %s where product_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)))) {
						$obj = new preparedStatement(sprintf('insert into %s(product_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$curr,$dest));
						$status = $obj->execute();
					}
				}
			} else {
				$status = false;
				$this->addError('Could not locate the destination folder');
			}
		}
		else {
			if ($src == 0 || $dest < 0) {
				$this->addMessage('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$src = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_junction,$src));
			$sql = sprintf('select * from %s where folder_id = %d order by sequence limit %d,1',$this->m_junction,$src['folder_id'],$dest);
			$dest = $this->fetchSingle($sql);
			$this->logMessage("moveArticle",sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
			if (count($src) == 0 || count($dest) == 0) {
				$status = false;
				$this->addMessage('Either the source or destination article was not found');
			}
			else {
				//
				//	swap the order of the images
				//
				$this->logMessage('moveArticle', sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
				$this->beginTransaction();
				$sql = sprintf('update %s set sequence = %d where id = %s',
					$this->m_junction, $src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
				if ($this->execute($sql)) {
					$this->resequence($src['folder_id']);
					$this->commitTransaction();
					$status = true;
				}
				else {
					$this->rollbackTransaction();
					$status = false;
				}					
			}
		}
		return $this->ajaxReturn(array(
				'status'=>$status?'true':'false'
		));
	}
	
	function resequence($folder) {
		$this->logMessage('resequence', "resequencing folder $folder", 2);
		$articles = $this->fetchAll(sprintf('select * from %s where folder_id = %d order by sequence',$this->m_junction,$folder));
		$seq = 10;
		foreach($articles as $article) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_junction,$seq,$article['id']));
			$seq += 10;
		}
	}

	function deleteArticle() {
		$form = new Forms();
		$form->init($this->getTemplate('deleteItem'));
		$flds = $form->buildForm($this->getFields('deleteItem'));
		if (count($_REQUEST) > 0 && $_REQUEST['j_id'] == 0)
			$form->getField('one')->addAttribute('disabled','disabled');
		$form->addData($_REQUEST);
		if (count($_REQUEST) > 0 && array_key_exists('deleteItem',$_REQUEST)) {
			if ($form->validate()) {
				$type = $form->getData('action');
				switch($type) {
					case 'cancel':
						return $this->ajaxReturn(array('status'=>'true','code'=>'closePopup();'));
						break;
					case 'all':
						$this->execute(sprintf('delete from %s where product_id = %d',$this->m_junction,$_REQUEST['p_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['p_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalar(sprintf('select count(0) from %s where product_id = %d',$this->m_junction,$_REQUEST['p_id']));
						if ($ct == 0)
							$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['p_id']));
						break;
					default:
						break;
				}
				$form->init($this->getTemplate('deleteItemResult'));
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function deleteContent() {
		if (array_key_exists('p_id',$_REQUEST)) {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where folder_id = %d',$this->m_junction,$_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Products are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalar(sprintf('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if (!$this->deleteCheck('product',$_REQUEST['p_id'],$inUse)) {
				$this->addError('Some Pages or Templates still use this folder');
				foreach($inUse as $key=>$value) {
					$this->addError($value);
				}
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$mptt = new mptt($this->m_tree);
			$mptt->delete($_REQUEST['p_id']);
			return $this->ajaxReturn(array('status'=>'true'));
		}
	}

	function myProductList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::productList($_REQUEST['f_id'],$this->getTemplate('productByFolder'),$this->getFields('productByFolder'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function assemblyRow($rowId = null,$owner = true) {
		$frm = new Forms();
		$frm->init($this->getTemplate('assemblyRow'));
		$flds = $frm->buildForm($this->getFields('assemblyRow'));
		if ((count($_REQUEST) > 0 && array_key_exists('assemblyRow',$_REQUEST)) || $rowId > 0) {
			$id = $rowId == null ? $_REQUEST['assemblyRow'] : $rowId;
			if ($owner) 
				$rec = $this->fetchSingle(sprintf('select a.*, p.name, p.code, a.subproduct_id as edit_id from product_by_package a, product p where a.id = %d and p.id = a.subproduct_id',$id));
			else
				$rec = $this->fetchSingle(sprintf('select a.*, p.name, p.code, a.product_id as edit_id from product_by_package a, product p where a.id = %d and p.id = a.product_id',$id));
			$frm->addData($rec);
			if ($rowId == null)
				return $this->ajaxReturn(array('status'=>'true','html'=>$frm->show()));
			else
				return $frm->show();
		} else if (count($_REQUEST) > 0 && array_key_exists('addRow',$_REQUEST)) {
			$rec = $this->fetchSingle(sprintf('select 0 as id, p.name, p.code, p.id as subproduct_id, 0 as sequence, 0 as quantity from product p where p.id = %d and deleted = 0',$_REQUEST['addRow']));
			$frm->addData($rec);
			return $this->ajaxReturn(array('status'=>'true','html'=>$frm->show()));
		}
		else
			return false;
	}

	function assembledFromRow_dnu($rowId = null) {
		$frm = new Forms();
		$frm->init($this->getTemplate('assembledFromRow'));
		$flds = $frm->buildForm($this->getFields('assembledFromRow'));
		if ((count($_REQUEST) > 0 && array_key_exists('assembledFromRow',$_REQUEST)) || $rowId > 0) {
			$id = $rowId == null ? $_REQUEST['assembledFromRow'] : $rowId;
			$rec = $this->fetchSingle(sprintf('select a.*, p.name, p.code from product_by_package a, product p where a.id = %d and p.id = a.product_id',$id));
			$frm->addData($rec);
			if ($rowId == null)
				return $this->ajaxReturn(array('status'=>'true','html'=>$frm->show()));
			else
				return $frm->show();
		} 
		return false;
	}

	function myCouponList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::couponList($_REQUEST['f_id'],$this->getTemplate('couponByProductList'),$this->getFields('couponByProductList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function myBlogList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::blogList($_REQUEST['f_id'],$this->getTemplate('blogByProductList'),$this->getFields('blogByProductList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function addFolder($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addFolder'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function addProduct($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addProduct'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function myStoreList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::storeList($_REQUEST['f_id'],$this->getTemplate('storeList'),$this->getFields('storeByProductList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function myEventList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::eventList($_REQUEST['f_id'],$this->getTemplate('eventList'),$this->getFields('eventByProductList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function myNewsList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::newsList($_REQUEST['f_id'],$this->getTemplate('newsList'),$this->getFields('newsByProductList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function edit() {
		if (array_key_exists('e_id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$_REQUEST['e_id']))) {
			$f = new Forms();
			$f->init($this->getTemplate('editItem'));
			$f->addData($data);
			return $this->show($f->show());
		}
		else
			return $this->show();
	}

	function showInventory() {
		$id = array_key_exists('i_id',$_REQUEST) ? $_REQUEST['i_id'] : 0;
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$count = $this->fetchScalar(sprintf('select count(*) from product_inventory where product_id = %d and deleted = 0',$id));
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
				array('url'=>'/modit/ajax/showInventory/product','destination'=>'tabs-8')
			);
		$start = ($pageNum-1)*$perPage;
		$form = new Forms();
		$form->init($this->getTemplate('showInventory'));
		$fields = $form->buildForm($this->getFields('showInventory'));
		$form->addTag('pagination',$pagination,false);
		$form->addTag('i_id',$id);
		$inv = $this->fetchAll(sprintf('select p.* from product_inventory p where p.product_id = %d and p.deleted = 0 order by start_date desc,id limit %d,%d',$id,$start,$perPage));
		$temp = array();
		foreach($inv as $rec) {
			$temp[] = $this->inventoryRow($rec['id']);
		}
		$form->addTag('inventory',implode('',$temp),false);
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function inventoryRow($rowId = null) {
		$this->logMessage('inventoryRow',sprintf('rowId = [%s]', $rowId), 2);
		$frm = new Forms();
		$frm->init($this->getTemplate('inventoryRow'));
		$flds = $this->getFields('inventoryRow');
		$flds['start_date']['id'] = sprintf('start_date_%d',$frm->getData('random'));
		$flds['end_date']['id'] = sprintf('end_date_%d',$frm->getData('random'));
		$flds = $frm->buildForm($flds);
		if ((count($_REQUEST) > 0 && array_key_exists('inventoryRow',$_REQUEST)) || $rowId > 0) {
			$id = $rowId == null ? $_REQUEST['inventoryRow'] : $rowId;
			if ($rec = $this->fetchSingle(sprintf('select p.* , l1.value as color_name, l2.value as size_name, o.teaser from product_inventory p left join code_lookups l1 on l1.id = p.color left join code_lookups l2 on l2.id = p.size left join product_options o on o.id = p.options_id where p.id = %d and p.deleted = 0',$id))) {
				$this->logMessage('inventoryRow',sprintf('this data [%s] rec[%s]',print_r($frm->getAllData(),true),print_r($rec,true)),4);
				$frm->addData($rec);
				$this->logMessage('inventoryRow',sprintf('this data [%s]',print_r($frm->getAllData(),true)),4);
			}
			if ($rowId == null)
				return $this->ajaxReturn(array('status'=>'true','html'=>$frm->show()));
			else
				return $frm->show();
		} else if (count($_REQUEST) > 0 && array_key_exists('addRow',$_REQUEST)) {
			$frm->deleteElement('delete');
			$this->logMessage('inventoryRow',sprintf('add return form [%s]',print_r($frm,true)),3);
			return $this->ajaxReturn(array('status'=>'true','html'=>$frm->show()));
		}
		else
			return false;
	}

	function inventoryAudit() {
		if (array_key_exists('i_id',$_REQUEST) && $prod = $this->fetchSingle(sprintf('select p.*, i.id as inventory_id from product p, product_inventory i where i.id = %d and p.id = i.product_id',$_REQUEST['i_id']))) {
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $this->m_perrow;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			$count = $this->fetchScalar(sprintf('select count(i.id) from product_inventory_audit i where i.inventory_id = %d', $_REQUEST['i_id']));
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
					'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
					array('url'=>'/modit/ajax/inventoryAudit/product','destination'=>'inventoryAudit')
				);
			$start = ($pageNum-1)*$perPage;
			$form = new Forms();
			$form->init($this->getTemplate('inventoryAudit'),$flds['options']);
			$form->addData($prod);
			$flds = $form->buildForm($this->getFields('inventoryAudit'));
			$form->addTag('pagination',$pagination,false);
			$subForm = new Forms();
			$subForm->init($this->getTemplate('inventoryAuditRow'));
			$subflds = $subForm->buildForm($this->getFields('inventoryAuditRow'));
			$data = $this->fetchAll(sprintf('select * from product_inventory_audit i where i.inventory_id = %d order by id desc limit %d,%d', $_REQUEST['i_id'],$start,$perPage));
			$output = array();
			foreach($data as $rec) {
				$subForm->addData($rec);
				$output[] = $subForm->show();
			}
			$form->addTag('audit',implode('',$output),false);
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		}
		else return $this->ajaxReturn(array('status'=>'false'));
	}

	function editInventory() {
		if (!(array_key_exists('i_id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from product_inventory where id = %d',$_REQUEST['i_id'])))) {
			$data = array('id'=>0,'product_id'=>$_REQUEST['product_id'],'options_id'=>0,'color'=>0,'size'=>0);
			unset($fields['delete']);
		}
		$form = new Forms();
		$form->init($this->getTemplate('editInventory'),$fields['options']);
		$fields = $form->buildForm($this->getFields('editInventory')); 
		$form->addData($data);
		if (array_key_exists('editInventory',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['i_id'];
				$this->beginTransaction();
				$status = true;
				if (array_key_exists('delete',$_POST)) {
					if ($id > 0) {
						$this->logMessage('editInventory',sprintf('deleting inventory id [%d]',$id),2);
						$rec = $this->fetchSingle(sprintf('select * from product_inventory where id=%d',$id));
						$status = $status && Inventory::auditInventory($rec['id'], -$rec['quantity'], 0, 'Inventory Deleted');
						//$obj = new preparedStatement('insert into product_inventory_audit(inventory_id,audit_date,quantity,comment) values(?,?,?,?)');
						//$obj->bindParams(array('dsds',$id,date(DATE_ATOM),-$rec['quantity'],'Inventory Deleted'));
						//$status = $obj->execute();
						$status = $status && $this->execute(sprintf('update product_inventory set deleted = 1, quantity = 0 where id = %d',$id));
					}
				} else {
					$this->logMessage('editInventory',sprintf('updating inventory id [%d] quantity [%d]',$id,$_POST['quantity']),2);
					$fields = array('quantity'=>$form->getData('quantity'),
						'start_date'=>$form->getData('start_date'),
						'end_date'=>$form->getData('end_date'),
						'comments'=>$form->getData('comments'),
						'product_id'=>$form->getData('product_id'),
						'options_id'=>$form->getData('options_id'),
						'color'=>$form->getData('color'),
						'size'=>$form->getData('size'));
					if ($id > 0) {
						$rec = $this->fetchSingle(sprintf('select * from product_inventory where id=%d',$id));
						$where = array();
						foreach($fields as $fld=>$tmp) {
							$where[] = sprintf('%s = ?',$fld);
						}
						$obj = new preparedStatement(sprintf('update product_inventory set %s where id = %d',implode(',',$where),$id));
						$obj->bindParams(array_merge(array(str_repeat('s',count($fields))),array_values($fields)));
						$status = $status && $obj->execute();
					}
					else {
						$rec = array('quantity'=>0);
						$obj = new preparedStatement(sprintf('insert into product_inventory(%s) values(%s)',implode(',',array_keys($fields)),'?'.str_repeat(',?',count($fields)-1)));
						$obj->bindParams(array_merge(array(str_repeat('s',count($fields))),array_values($fields)));
						$status = $status && $obj->execute();
						$id = $this->insertId();
					}
					if ($fields['quantity']-$rec['quantity'] != 0) {
						$status = $status && Inventory::auditInventory($id, $fields['quantity']-$rec['quantity'], 0, sprintf('Inventory Updated [%s]',$fields['comments']));
					}
				}
				if ($status) {
					$this->commitTransaction();
					$form->init($this->getTemplate('editInventorySuccess'));
					$form->setData('valid',1);
					$form->addTag('product_id',$form->getData('product_id'));	// can't use hidden field directly
				}
				else {
					$this->rollbackTransaction();
					$form->addError('An Error occurred');
				}
			}
			else {
				$form->addError('Form validation failed');
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function inventorySearch($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('inventorySearchForm'),array('name'=>'inventorySearchForm','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('inventorySearchForm'));
		if (count($_POST) == 0 && array_key_exists('formData',$_SESSION) && array_key_exists('inventorySearchForm', $_SESSION['formData']))
			$_POST = $_SESSION['formData']['inventorySearchForm'];
		if (count($_POST) > 0 && array_key_exists('inventorySearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['inventorySearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'name':
						case 'code':
							if ($_POST['opt_'.$key] != null && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' p.%s %s "%s"',$key,$_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'inventory_date':
							$value = $form->getData($key);
							$this->logMessage('inventorySearch',sprintf('inventory_date [%s] fields [%s]',print_r($value,true),print_r($frmFields,true)),1);
							$srch[] = sprintf(' ((i.start_date <= "%s" and i.end_date >= "%s") or (i.start_date = "%s" and i.end_date = "0000-00-00"))',
								$value,$value,$value,$value);
							break;
						case 'quantity':
						case 'start_date':
						case 'end_date':
							if ($_POST['opt_'.$key] != null && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like') {
									$this->addError('Like is not supported for this dates/numeric fields');
								}
								else
									$srch[] = sprintf(' i.%s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'folder':
							if (($value = $form->getData($key)) > 0) {
								$srch[] = sprintf(' p.id in (select product_id from %s where folder_id = %d) ', $this->m_junction, $value);
							}
							break;
							break;
						case 'deleted':
							if (($value = $form->getData($key)) != null)
								$srch[] = sprintf(' i.%s = %s',$key,$this->escape_string($value));
							break;
						default:
							break;
					}
				}
				if (count($srch) > 0) {
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					$perPage = $this->m_perrow;
					if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
					$count = $this->fetchScalar(sprintf('select count(i.id), p.code, p.name from product_inventory i, product p where p.id = i.product_id and %s', implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
							'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
							array('url'=>'/modit/ajax/inventorySearchForm/product','destination'=>'middleContent')
						);
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'desc';
					$sortby = 'created';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						$sortorder = $_POST['sortorder'];
					}
					$sql = sprintf('select n.*, j.id as j_id from %s n, %s j where n.id = j.product_id and j.id = (select min(j1.id) from %s j1 where j1.product_id = n.id) and %s order by %s %s limit %d,%d',
						 $this->m_content, $this->m_junction, $this->m_junction, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);

					$sql = sprintf('select i.*, p.code, p.name from product_inventory i, product p where p.id = i.product_id and %s order by %s %s limit %d,%d',
						 implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);

					$recs = $this->fetchAll($sql);
					$this->logMessage('inventorySearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					$frm = new Forms();
					$frm->init($this->getTemplate('inventoryList'),array());
					$tmp = $frm->buildForm($this->getFields('inventoryList'));
					foreach($recs as $article) {
						$frm->addData($article);
						$articles[] = $frm->show();
					}
					$form->addTag('articles',implode('',$articles),false);
					$form->addTag('pagination',$pagination,false);
				}
			}
		}
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function pricingRow($passedId = null, $data = null, $returnForm = false) {
		$this->logMessage('pricingRow',sprintf('passed [%s] data [%s]',$passedId, print_r($data,true)),1);
		if ($passedId == null)
			$id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
		else $id = $passedId;
		if (!is_array($data))	// pass an optional array of populated data
			if (!($data = $this->fetchSingle(sprintf('select p.* from product_pricing p where id = %d',$id))))
				$data = array('id'=>0,'delete'=>-1);
			else $data['delete'] = $data['id'];
		$tmp = new Forms();
		$tmp->init($this->getTemplate('pricingRow'));
		$tmpFlds = $tmp->buildForm($this->getFields('pricingRow'));
		$tmp->addData($data);
		if ($passedId == null) {
			$this->logMessage('pricingRow',sprintf('executing ajax return [%s]',$passedId),1);
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp->show()));
		}
		else {
			$this->logMessage('pricingRow',sprintf('executing normal return [%s]',$passedId),1);
			if ($returnForm)
				return $tmp;
			else
				return $tmp->show();
		}
	}

	function rebuildForms($form) {
		//
		//	reconstruct the pricing html from the data array passed in
		//
		$pricing = $form->getData('pricing');
		$this->logMessage("rebuildForms",sprintf("rebuild pricing [%s] [%s]",print_r($pricing,true),print_r($form->getAllData(),true)),2);
		$tmp = array();
		$ids = $pricing['id'];
		foreach($ids as $key=>$value) {
			$this->logMessage("rebuildForms",sprintf("key [%s] row [%s]",$key,$value),2);
			$tmp[] = $this->pricingRow(-1,array(
				'id'=>$value,
				'min_quantity'=>$pricing['min_quantity'][$key],
				'max_quantity'=>$pricing['max_quantity'][$key],
				'price'=>$pricing['price'][$key],
				'sale_price'=>$pricing['sale_price'][$key],
				'shipping'=>$pricing['shipping'][$key],
				'shipping_type'=>$pricing['shipping_type'][$key],
				'delete'=>array_key_exists('delete',$pricing) ? $pricing['delete'][$key] : 0,
			));
		}
		$form->setData('pricing',implode('',$tmp));
		return $form;
	}

	function subformValidate(&$form) {
		//
		//	reconstruct the pricing html from the data array passed in
		//
		$pricing = $form->getData('pricing');
		$this->logMessage("subformValidate",sprintf("rebuild pricing [%s]",print_r($pricing,true)),2);
		$tmp = array();
		$ids = $pricing['id'];
		$status = true;
		$deletes = array_key_exists('delete',$pricing) ? '|'.implode('|',$pricing['delete']).'|' : '||';
		foreach($ids as $key=>$value) {
			$this->logMessage("subformValidate",sprintf("key [%s] row [%s]",$key,$value),2);
			$subform = $this->pricingRow(-1,array(
				'id'=>$value,
				'min_quantity'=>$pricing['min_quantity'][$key],
				'max_quantity'=>$pricing['max_quantity'][$key],
				'price'=>$pricing['price'][$key],
				'sale_price'=>$pricing['sale_price'][$key],
				'shipping'=>$pricing['shipping'][$key],
				'shipping_type'=>$pricing['shipping_type'][$key],
				'message'=>$pricing['message'][$key],
				'delete'=>$value,
			),true);
			//
			//	don't validate a record we will be deleting anyway
			//
			if (!($value == 0 && $pricing['min_quantity'][$key] == 0 && $pricing['max_quantity'][$key] == 0))
				if (strpos($deletes,'|'.$value.'|') === false)
					$status = $subform->validate() && $status;
			$tmp[] = $subform->show();
		}
		$form->setData('pricing',implode('',$tmp));
		return $status;
	}

	function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('productSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['productSearchForm'];
			$msg = '';
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where (enabled = 0 or published = 0) and deleted = 0',$this->m_content));
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing latest products added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'enabled'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow,'deleted'=>0);
				$msg = "Showing disabled products";
			}
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

	function getHeader() {
		$form = new Forms();
		$form->init($this->getTemplate('header'));
		$flds = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST))
			$form->addData($_POST);
		return $form->show();
	}

	function hasFunctionAccess($method) {
		$this->logMessage("hasFunction Access",sprintf("looking for method [$method]"),2);
		if (parent::hasFunctionAccess($method)) return true;
		return true;
	}

	function editOption() {
		if (!array_key_exists('o_id',$_REQUEST))
			return $this->ajaxReturn(array('status'=>'false','html'=>'No id passed'));
		$p_id = $_REQUEST['p_id'];
		$o_id = $_REQUEST['o_id'];
		if (!($data = $this->fetchSingle(sprintf('select * from product_options where id = %d and product_id = %d',$o_id,$p_id)))) {
			$data = array('id'=>0,'product_id'=>$p_id);
		}
		$form = new Forms();
		$form->init($this->getTemplate('editOption'),array('name'=>'editOption','action'=>'editOption'));
		$frmFields = $this->getFields('editOption');

		$customFields = new custom();
		if (method_exists($customFields,'productOptionsDisplay')) {
			$custom = $customFields->productOptionsDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-optionscustom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}
		$data['colors'] = $this->fetchScalarAll(sprintf('select type_id from product_options_info, code_lookups c where c.id = type_id and options_id = %d and options_type="color" order by sort,value',$data['id']));
		$data['sizes'] = $this->fetchScalarAll(sprintf('select type_id from product_options_info, code_lookups c where c.id = type_id and options_id = %d and options_type="size" order by sort,value',$data['id']));
		$frmFields = $form->buildForm($frmFields);
		$form->addData($data);
		$form->addTag('p_id',$p_id);
		if (count($_POST) > 0 && array_key_exists('editOption',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						if ($data['id'] > 0)
							$flds[sprintf('%s = ?',$fld['name'])] = $form->getData($fld['name']);
						else
							$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				if ($data['id'] == 0) {
					$stmt = $this->prepare(sprintf('insert into product_options(%s) values(%s)', implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$stmt = $this->prepare(sprintf('update product_options set %s where id = %d', implode(',',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				$this->beginTransaction();
				$status = $stmt->execute();
				if ($status) {
					if ($data['id'] == 0)
						$data['id'] = $this->insertId();
					$colors = array(0);
					if (array_key_exists('colors',$_REQUEST)) {
						$colors = array_merge($colors,$_REQUEST['colors']);
					}
					$status = $status && $this->execute(sprintf('delete from product_options_info where options_id = %d and options_type = "color" and type_id not in (%s)',$data['id'],implode(',',$colors)));
					$new = $this->fetchAll(sprintf('select * from code_lookups where type="color" and id in (%s) and id not in (select type_id from product_options_info where options_id = %d and options_type = "color")',implode(',',$colors),$data['id']));
					$stmt = $this->prepare(sprintf('insert into product_options_info(options_id,options_type,type_id) values(?,"color",?)'));
					foreach($new as $key=>$value) {
						$status = $status && $stmt->bindParams(array('ii',$data['id'],$value['id']));
						$status = $status && $stmt->execute();
					}
					$sizes = array(0);
					if (array_key_exists('sizes',$_REQUEST)) {
						$sizes = array_merge($sizes,$_REQUEST['sizes']);
					}
					$status = $status && $this->execute(sprintf('delete from product_options_info where options_id = %d and options_type = "size" and type_id not in (%s)',$data['id'],implode(',',$sizes)));
					$new = $this->fetchAll(sprintf('select * from code_lookups where type="size" and id in (%s) and id not in (select type_id from product_options_info where options_id = %d and options_type = "size")',implode(',',$sizes),$data['id']));
					$stmt = $this->prepare(sprintf('insert into product_options_info(options_id,options_type,type_id) values(?,"size",?)'));
					foreach($new as $key=>$value) {
						$status = $status && $stmt->bindParams(array('ii',$data['id'],$value['id']));
						$status = $status && $stmt->execute();
					}
				}
				if ($status) {
					$this->commitTransaction();
					$form->init($this->getTemplate('editOptionSuccess'));
				}
				else {
					$this->rollbackTransaction();
					$this->addError('An error occurred updating the database');
				}
			}
			else $form->addFormError('Form validation Failed');
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function loadOptions($owner = null) {
		if (array_key_exists('o_id',$_REQUEST) && is_null($owner)) {
			$options = $this->fetchAll(sprintf('select o.* from product_options o where o.product_id = %d and deleted = 0 order by o.sequence',$_REQUEST['o_id']));
		}
		else
			$options = $this->fetchAll(sprintf('select o.* from product_options o where o.product_id = %d and deleted = 0 order by o.sequence',$owner));
		$form = new Forms();
		$form->init($this->getTemplate('loadOptions'));
		$flds = $form->buildForm($this->getFields('loadOptions'));
		$return = array();
		foreach($options as $rec) {
			$form->addData($rec);
			$return[] = $form->show();
		}
		if (is_null($owner)) {
			return $this->ajaxReturn(array('status'=>'true','html'=>implode('',$return)));
		}
		else
		return implode('',$return);
	}

	function loadPricing($owner = null) {
		if (array_key_exists('o_id',$_REQUEST) && is_null($owner)) {
			$recs = $this->fetchAll(sprintf('select * from product_pricing where product_id = %d order by min_quantity',$_REQUEST['o_id']));
		}
		else
			$recs = $this->fetchAll(sprintf('select * from product_pricing where product_id = %d order by min_quantity',$owner));
		$form = new Forms();
		$form->init($this->getTemplate('loadPricing'));
		$flds = $form->buildForm($this->getFields('loadPricing'));
		$pricing = array();
		foreach($recs as $price) {
			$form->addData($price);
			$pricing[] = $form->show();	//$this->pricingRow($price['id']);
		}
		if (!is_null($owner))
			return implode('',$pricing);
		else
			return $this->ajaxReturn(array('status'=>'true','html'=>implode('',$pricing)));
	}

	function loadRecurring($owner = null) {
		if (array_key_exists('o_id',$_REQUEST) && is_null($owner))
			$sql = sprintf('select r.*, l.code from product_recurring r, code_lookups l where r.product_id = %d and l.id = r.lookup_id order by sequence',$_REQUEST['o_id']);
		else
			$sql = sprintf('select r.*, l.code from product_recurring r, code_lookups l where r.product_id = %d and l.id = r.lookup_id order by sequence',$owner);
		$recs = $this->fetchAll($sql);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] recs [%d]',$sql,count($recs)),2);
		$form = new Forms();
		$form->init($this->getTemplate('loadRecurring'));
		$flds = $form->buildForm($this->getFields('loadRecurring'));
		$recurring = array();
		foreach($recs as $price) {
			$form->addData($price);
			$recurring[] = $form->show();
		}
		if (!is_null($owner))
			return implode('',$recurring);
		else
			return $this->ajaxReturn(array('status'=>'true','html'=>implode('',$recurring)));
	}

	function editPricing() {
		if (!array_key_exists('o_id',$_REQUEST))
			return $this->ajaxReturn(array('status'=>'false','html'=>'No id passed'));
		$p_id = $_REQUEST['p_id'];
		$o_id = $_REQUEST['o_id'];
		if (!($data = $this->fetchSingle(sprintf('select * from product_pricing where id = %d and product_id = %d',$o_id,$p_id)))) {
			$data = array('id'=>0,'product_id'=>$p_id);
		}
		$form = new Forms();
		$form->init($this->getTemplate('editPricing'),array('name'=>'editPricing','action'=>'editPricing'));
		$frmFields = $form->buildForm($this->getFields('editPricing'));
		$form->addData($data);
		$form->addTag('p_id',$p_id);
		if (count($_POST) > 0 && array_key_exists('editPricing',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						if ($data['id'] > 0)
							$flds[sprintf('%s = ?',$fld['name'])] = $form->getData($fld['name']);
						else
							$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				if ($data['id'] == 0) {
					$stmt = $this->prepare(sprintf('insert into product_pricing(%s) values(%s)', implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$stmt = $this->prepare(sprintf('update product_pricing set %s where id = %d', implode(',',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				$this->beginTransaction();
				if ($stmt->execute()) {
					$this->commitTransaction();
					$form->init($this->getTemplate('editPricingSuccess'));
				}
				else {
					$this->rollbackTransaction();
					$this->addError('An error occurred updating the database');
				}
			}
			else $form->addFormError('Form validation Failed');
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function deletePricing() {
		if (array_key_exists('p_id',$_REQUEST) && array_key_exists('o_id',$_REQUEST)) {
			$this->execute(sprintf('delete from product_pricing where id = %d and product_id = %d',$_REQUEST['p_id'],$_REQUEST['o_id']));
			$status = 'true';
		}
		else $status = 'false';
		return $this->ajaxReturn(array('status'=>$status,'html'=>'<div></div>'));
	}

	function deleteOption() {
		if (array_key_exists('p_id',$_REQUEST) && array_key_exists('o_id',$_REQUEST)) {
			$this->execute(sprintf('update product_options set deleted = 1 where id = %d and product_id = %d',$_REQUEST['p_id'],$_REQUEST['o_id']));
			$status = 'true';
		}
		else $status = 'false';
		return $this->ajaxReturn(array('status'=>$status,'html'=>'<div></div>'));
	}

	function resize($original,$size) {
		$this->logMessage("resize",sprintf("original [%s] size [%s]",$original,print_r($size,true)),2);
		$src = $original;	//switch from url to local file
		$test = str_replace($this->M_ROOT,"",$src);	// strip to file name and possibly subdirectory
		$this->logMessage("resize",sprintf("src [%s] test [%s]",$src,$test),2);
		if (($i = strrpos($test, '/')) !== false) {
			$filename = substr($test, $i + 1);
			$folder = substr($test,0,$i);
		}
		else {
			$filename = $test;
			$folder = "";
		}
		$this->logMessage("resize",sprintf("filename [%s] folder [%s]",$filename,$folder),2);
		$img = new imageresize("..".$src);
		if (!array_key_exists("proportional",$size)) $size["proportional"] = true;
		$tmp = "..".str_replace('originals',$size['dir'],$this->M_ROOT);
		if ($folder != "") {
			$this->logMessage(__FUNCTION__,sprintf("folder test [%s] exists test [%s] exists [%s]", $folder, $tmp.$folder, file_exists($tmp.$folder)),1);
			if (!file_exists($tmp.$folder)) {
				$full = explode('/',$folder);
				$root = $tmp;
				foreach($full as $tmp) {
					$this->logMessage('resize',sprintf('root [%s] tmp [%s]',$root,$tmp),1);
					$root = $root.$tmp.'/';
					if (!file_exists($root))
						mkdir($root);
				}
			}
			$tmp = "..".str_replace('originals',$size['dir'],$this->M_ROOT);
			$tmp .= $folder."/";
		}
		//else $tmp = $size['dir'];
		$this->logMessage("resize",sprintf("src [%s] dest [%s]",$src,$tmp.$filename),2);
		$img->resize(
				$tmp.$filename,
				array_key_exists('width',$size) ? $size['width'] : 0,
				array_key_exists('height',$size) ? $size['height'] : 0,
				array_key_exists('proportional',$size) ? $size['proportional'] : true,
				array_key_exists('crop',$size) ? $size['crop'] : false);
		unset($img);
	}

	function buildAltSizes($original) {
		if (strlen($original) > 0) {
			$sizes = $GLOBALS['product'];
			foreach($sizes as $size) {
				$this->resize($original,$size);
			}
		}
	}

	function resizeAll() {
		$records = $this->fetchAll('select * from product');
		foreach($records as $key=>$record) {
			set_time_limit(0);
			$this->buildAltsizes($record['image1']);
			$this->buildAltsizes($record['image2']);
			$this->buildAltsizes($record['image3']);
			$this->buildAltsizes($record['image4']);
		}
		return $this->ajaxReturn(array('status'=>'true'));
	}

	function editRecurring() {
		if (!array_key_exists('o_id',$_REQUEST))
			return $this->ajaxReturn(array('status'=>'false','html'=>'No id passed'));
		$p_id = $_REQUEST['p_id'];
		$o_id = $_REQUEST['o_id'];
		if (!($data = $this->fetchSingle(sprintf('select * from product_recurring where id = %d and product_id = %d',$o_id,$p_id)))) {
			$data = array('id'=>0,'product_id'=>$p_id);
		}
		$form = new Forms();
		$form->init($this->getTemplate('editRecurring'),array('name'=>'editRecurring','action'=>'editRecurring'));
		$frmFields = $form->buildForm($this->getFields('editRecurring'));
		$form->addData($data);
		$form->addTag('p_id',$p_id);
		if (count($_POST) > 0 && array_key_exists('editRecurring',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						if ($data['id'] > 0)
							$flds[sprintf('%s = ?',$fld['name'])] = $form->getData($fld['name']);
						else
							$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				if ($data['id'] == 0) {
					$stmt = $this->prepare(sprintf('insert into product_recurring(%s) values(%s)', implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$stmt = $this->prepare(sprintf('update product_recurring set %s where id = %d', implode(',',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				$this->beginTransaction();
				if ($stmt->execute()) {
					$this->commitTransaction();
					$form->init($this->getTemplate('editRecurringSuccess'));
				}
				else {
					$this->rollbackTransaction();
					$this->addError('An error occurred updating the database');
				}
			}
			else $form->addFormError('Form validation Failed');
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function moveRecurring() {
		$src = 0;
		$dest = 0;
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($src == 0 || $dest < 0) {
			$this->addMessage('Either source or destination was missing');
			return $this->ajaxReturn(array('status' => 'false'));
		}
		$src = $this->fetchSingle(sprintf('select * from product_recurring where id = %d',$src));
		$sql = sprintf('select * from product_recurring where product_id = %d order by sequence limit %d,1',$src['product_id'],$dest);
		$dest = $this->fetchSingle($sql);
		$this->logMessage("moveArticle",sprintf("move src [%s] to dest [%s]",print_r($src,true),print_r($dest,true)),2);
		if (count($src) == 0 || count($dest) == 0) {
			$status = false;
			$this->addMessage('Either the source or destination article was not found');
		}
		elseif ($src['product_id'] != $dest['product_id']) {
			$status = false;
			$this->addMessage('An internal error occurred [Items do not belong to the same product]');
		}
		else {
			//
			//	swap the order of the images
			//
			$this->logMessage(__FUNCTION__, sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
			$this->beginTransaction();
			$sql = sprintf('update product_recurring set sequence = %d where id = %s',
				$src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
			if ($this->execute($sql)) {
				$items = $this->fetchAll(sprintf('select * from product_recurring where product_id = %d order by sequence',$src['product_id']));
				$seq = 10;
				foreach($items as $item) {
					$this->execute(sprintf('update product_recurring set sequence = %d where id = %d',$seq,$item['id']));
					$seq += 10;
				}
				$this->commitTransaction();
				$status = true;
			}
			else {
				$this->rollbackTransaction();
				$status = false;
			}					
		}
		return $this->ajaxReturn(array(
				'status'=>$status?'true':'false'
		));
	}

	function getVideo($b_id = 0,$fromPost) {
		$outer = new Forms();
		$outer->init($this->getTemplate('video'));
		if ($fromPost)
			$data = array_key_exists('video',$_POST) ? $_POST['video'] : array('id'=>0);
		else
			if (!$data = $this->fetchSingle(sprintf('select * from videos where owner_id = %d and owner_type = "product"', $b_id)))
				$data = array('id'=>0);
		$flds = $outer->buildForm($this->getFields('video'));
		$outer->addData($data);
		return $outer->show();
	}

	function validateVideo(&$form) {
		if (strlen($_POST['video']['url']) == 0 && strlen($_POST['video']['embed_code']) == 0) {
			return true;
		}
		$outer = new Forms();
		$outer->init($this->getTemplate('video'));
		$flds = $outer->buildForm($this->getFields('video'));
		$outer->addData($_POST['video']);
		$valid = $outer->validate();
		$msg = $outer->getFormErrors();
		foreach($msg as $key=>$value) {
			$form->addFormError($value);
		}
		return $valid;
	}
	
	function saveVideo($id) {
		if (!array_key_exists("video",$_POST)) return true;
		if (strlen($_POST['video']['url']) == 0 && strlen($_POST['video']['embed_code']) == 0) {
			if ($_POST['video']['v_id'] > 0)
				$this->execute(sprintf('delete from video where owner_type="product" and owner_id = %d'));
			return true;
		}
		$form = new Forms();
		$tmp = $form->buildForm($this->getFields('video'));
		$form->addData($_POST['video']);
		$flds = array();
		foreach($tmp as $key=>$fld) {
			if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
				$flds[str_replace(']','',str_replace('video[','',$fld['name']))] = $form->getData($fld['name']);
			}
		}
		if ($data = $this->fetchSingle(sprintf('select * from videos where owner_type="product" and owner_id = %d', $form->getData('v_id')))) {
			$stmt = $this->prepare(sprintf('update videos set %s=? where id = %d', implode('=?, ',array_keys($flds)),$data['id']));
		}
		else {
			$flds['owner_type'] = 'product';
			$flds['owner_id'] = $id;
			$stmt = $this->prepare(sprintf('insert into videos(%s) values(%s?)', implode(', ', array_keys($flds)), str_repeat('?, ',count($flds)-1)));
		}
		$stmt->bindParams(array_merge(array(str_repeat('s',count($flds))),array_values($flds)));
		$valid = $stmt->execute();
		return $valid;
	}

	function moveOption() {
		$src = 0;
		$dest = 0;
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($src == 0 || $dest < 0) {
			$this->addMessage('Either source or destination was missing');
			return $this->ajaxReturn(array('status' => 'false'));
		}
		$src = $this->fetchSingle(sprintf('select * from product_options where id = %d',$src));
		$sql = sprintf('select * from product_options where product_id = %d order by sequence limit %d,1',$src['product_id'],$dest);
		$dest = $this->fetchSingle($sql);
		$this->logMessage(__FUNCTION__,sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
		if (count($src) == 0 || count($dest) == 0) {
			$status = false;
			$this->addMessage('Either the source or destination article was not found');
		}
		else {
			$this->logMessage(__FUNCTION__, sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
			$this->beginTransaction();
			$sql = sprintf('update product_options set sequence = %d where id = %s',
				$src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
			if ($this->execute($sql)) {
				$this->resequenceOptions($src['product_id']);
				$this->commitTransaction();
				$status = true;
			}
			else {
				$this->rollbackTransaction();
				$status = false;
			}					
		}
		return $this->ajaxReturn(array(
				'status'=>$status?'true':'false'
		));
	}

	function resequenceOptions($p_id) {
		$recs = $this->fetchAll(sprintf('select * from product_options where product_id = %d order by sequence',$p_id));
		$seq = 10;
		foreach($recs as $rec) {
			if ($rec['sequence'] != $seq) $this->execute(sprintf('update product_options set sequence = %d where id = %d',$seq,$rec['id']));
			$seq += 10;
		}
		return;
	}

	function comments() {
		$outer = new Forms();
		$outer->init($this->getTemplate("comments"));
		$flds = $outer->buildForm($this->getFields("comments"));
		$p_id = array_key_exists("p_id",$_REQUEST) ? $_REQUEST["p_id"] : 0;
		$sql = sprintf("select * from product_reviews where product_id = %d",$p_id);
		if (array_key_exists('pager',$_REQUEST)) 
			$perPage = $_REQUEST['pager'];
		else
			$perPage = $this->m_perrow;
		$pagenum = array_key_exists("pagenum",$_REQUEST) ? $_REQUEST["pagenum"] : 1;
		$outer->setData('pager',$perPage);
		$outer->addData($_REQUEST);
		$count = $this->fetchScalar(sprintf('select count(id) from product_reviews where product_id = %d', $p_id));
		$pagination = $this->pagination($count, $perPage, $pagenum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
			'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
			array('url'=>'/modit/ajax/comments/product','destination'=>'tabs-18'));
		$outer->addTag("pagination",$pagination,false);
		$start = ($pagenum-1)*$perPage;
		$sql = sprintf('select * from product_reviews where product_id = %d order by created desc limit %d,%d', $p_id, $start, $perPage);
		$comments = $this->fetchAll($sql);
		$inner = new Forms();
		$inner->init($this->getTemplate("commentsRow"));
		$flds = $inner->buildForm($this->getFields("commentsRow"));
		$results = array();
		foreach($comments as $key=>$comment) {
			$inner->addData($comment);
			$results[] = $inner->show();
		}
		$outer->addTag("comments",implode("",$results),false);
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

	function editComment($byAjax = true) {
		$outer = new Forms();
		$outer->init($this->getTemplate("editComment"));
		$flds = $outer->buildForm($this->getFields("editComment"));
		$c_id = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		$comment = $this->fetchSingle(sprintf("select * from product_reviews where id = %d",$c_id));
		$outer->addData($comment);
		if (count($_POST) > 0 && array_key_exists("editComment",$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$tmp = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$tmp[$fld['name']] = $outer->getData($fld['name']);
					}
				}
				$stmt = $this->prepare(sprintf("update product_reviews set %s=? where id=%d", implode("=?, ",array_keys($tmp)),$c_id));
				$stmt->bindParams(array_merge(array(str_repeat("s", count($tmp))),array_values($tmp)));
				if ($stmt->execute()) {
					$outer->addFormSuccess("The review has been updated");
					$outer->init($this->getTemplate("editCommentSuccess"));
				}
			}
		}
		return $byAjax ? $this->ajaxReturn(array("status"=>true,"html"=>$outer->show())) : $outer->show();
	}

	function deleteComment() {
		$outer = new Forms();
		$outer->init($this->getTemplate("deleteComment"));
		$flds = $outer->buildForm($this->getFields("deleteComment"));
		$c_id = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		if ($comment = $this->fetchSingle(sprintf("select * from product_reviews where id = %d",$c_id))) {
			$outer->addData($comment);
			$this->execute(sprintf("delete from product_reviews where id = %d",$c_id));
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

	function approval() {
		$outer = new Forms();
		$outer->init($this->getTemplate("approval"));
		$tmp = $this->editComment(false);
		$outer->addTag("form",$tmp,false);
		return $this->show($tmp);
	}

	function inventoryColorSize() {
		$sz = $_REQUEST["type"];
		$o_id = $_REQUEST["o_id"];
		$s = new select();
		$s->addAttribute("name",$sz);
		$s->addOptions($this->fetchOptions(sprintf("select id,value from code_lookups where type='%s' and id in (select type_id from product_options_info where options_id = %d and options_type='%s') order by sort,value",$sz,$o_id,$sz)));
		return $this->ajaxReturn(array("html"=>$s->show(),"status"=>true));
	}

}

?>