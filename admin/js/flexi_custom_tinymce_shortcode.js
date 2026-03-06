(function ($) {
	'use strict';

	$(document).ready(
		function () {
			if (typeof (tinymce) !== 'undefined' && tinymce.PluginManager) {
				tinymce.PluginManager.add('flexi_admin_shortcodes', function (editor) {
					if (editor.id === 'purchase_email_body') {
						editor.addButton(
							'flexi_admin_shortcodes',
							{
								type: 'menubutton',
								text: 'Shortcodes',
								icon: false,
								menu: [
									{
										text: 'Customer Email',
										value: '{{customer_email}}',
										onclick: function () {
											editor.insertContent(this.value());
										}
									},
									{
										text: 'Customer Name',
										value: '{{customer_name}}',
										onclick: function () {
											editor.insertContent(this.value());
										}
									},
									{
										text: 'Coupon Used',
										value: '{{coupon_used}}',
										onclick: function () {
											editor.insertContent(this.value());
										}
									},
									{
										text: 'Recovered Products',
										value: '{{recovered_products}}',
										onclick: function () {
											editor.insertContent(this.value());
										}
									},
									{
										text: 'No. of Email Sent',
										value: '{{email_sent_count}}',
										onclick: function () {
											editor.insertContent(this.value());
										}
									},
									{
										text: 'Recovered Amount',
										value: '{{recovered_amt}}',
										onclick: function () {
											editor.insertContent(this.value());
										}
									},
									{
										text: 'Coupon Amount',
										value: '{{coupon_amt}}',
										onclick: function () {
											editor.insertContent(this.value());
										}
									},

									{
										text: 'Order Id',
										value: '{{order_id}}',
										onclick: function () {
											editor.insertContent(this.value());
										}
									},

								]
							}
						);
					}
					else if(editor.id === 'email_temp_body'){
						editor.addButton(
							'flexi_admin_shortcodes',
							{
								type: 'menubutton',
								text: 'Custom Shortcodes',
								icon: false,
								menu: [
								{
									text: 'Site URL',
									value: '{{site_url}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'Customer First Name',
									value: '{{user_firstname}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'Customer Last Name',
									value: '{{user_lastname}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'Customer Email',
									value: '{{user_email}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'Cart Link',
									value: '{{cart_checkout_url}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'Cart Details',
									value: '{{cart_details}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'Coupon Code',
									value: '{{coupon_code}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'Coupon Discount',
									value: '{{coupon_discount}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'Total Cost',
									value: '{{total_cost}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'From Email',
									value: '{{email_from}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								{
									text: 'From Name',
									value: '{{email_name}}',
									onclick: function () {
										editor.insertContent( this.value() );
									}
								},
								]
							}
						);
					
					}
				}
				);
			}
		});

		$( document ).ready(
			function () {
				$( '#shortcode_dropdown' ).on(
					'change',
					function () {
						var selectedShortcode     = $( this ).val();
						var $templateSubjectInput = $( '#template_subject' );
						var currentValue          = $templateSubjectInput.val();
	
						if (selectedShortcode) {
							// Check if the shortcode is already present in the input field.
							if ( ! currentValue.includes( selectedShortcode )) {
								$templateSubjectInput.val( currentValue + selectedShortcode );
							}
						}
					}
				);
			}
		);
	

		
		document.addEventListener(
			'DOMContentLoaded',
			function () {
				document.getElementById( 'save_new_template' ).addEventListener(
					'click',
					function (event) {
						var emailBody   = tinyMCE.get( 'email_temp_body' ).getContent();
						var isActive    = document.getElementById( 'status' ).checked;
						var template_id = document.getElementById( "template_id" ).value;
						var template_new = $( this ).data( 'page' );
		
						// Ensure cart_checkout_url exists in the email body if the template is active
						if (isActive) {
							if (!emailBody.includes('{{cart_checkout_url}}')) {
								alert("You cannot activate this template without including the {{cart_checkout_url}} in the email body.");
								event.preventDefault();
								return;
							} else {
								// Check if the template is not the default one, and prompt for confirmation
								if (template_id !== "1" && template_id !== "2") {
									var confirmActivation = confirm("Are you sure you want to activate this template? It will deactivate any other active templates of the same type.");
									if (!confirmActivation) {
										event.preventDefault(); // Prevent form submission
										return;
									}
								}
							}
						}
		
						// If deactivating the template, confirm the default template activation
						if (template_new !== "email-template-creation" && !isActive && (template_id !== "1" && template_id !== "2")) {
							var confirmDeactivation = confirm("Deactivating this template will activate the default template. Do you want to proceed?");
							if (!confirmDeactivation) {
								event.preventDefault(); // Prevent form submission
								return;
							}
						}
		
						// Check if another active template of the same type exists
						if (isActive) {
							var activeTemplates = document.querySelectorAll('.template-type[data-active="1"]'); // Assuming .template-type is the class for templates, and data-active is a custom attribute for active templates
							if (activeTemplates.length > 0) {
								var confirmSwitch = confirm("Another active template of the same type exists. Do you want to deactivate it and proceed?");
								if (!confirmSwitch) {
									event.preventDefault(); // Prevent form submission
								}
							}
						}
					}
				);
			}
		);
		

})(jQuery);
