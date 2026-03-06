(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */
  $(document).ready(function () {
    var ajaxurl = flexi_abandon_cart_recovery_obj.ajax_url;

    // $(".render_select2_multiselect_box").select2();

    // toggle GDPR message wp_editor
    var gdprShopCheckbox = $("#gdpr_shop_page");
    var gdprProductPage = $("#gdpr_product_page");
    var gdprmessageRow = $("#toggle_display_gdpr_message");

    function toggleGDPRMessage() {
      if (gdprShopCheckbox.is(":checked") || gdprProductPage.is(":checked")) {
        gdprmessageRow.css("display", "table-row-group");
      } else {
        gdprmessageRow.css("display", "none");
      }
    }
    toggleGDPRMessage();

    gdprShopCheckbox.on("change", toggleGDPRMessage);
    gdprProductPage.on("change", toggleGDPRMessage);
	function toggleTemplateTypeSections() {
		var selectedTemplateType = $("#template_type").val();
	  
		if (selectedTemplateType === "0") { // Coupon Template
		  $("#coupon_status_container").show();
		  $("#coupon_status").prop("required", true); // Make coupon status required
		  if ($("#coupon_status").is(":checked")) {
			$("#coupon_details").show();
			$("select.coupon-dropdown").prop("required", true);
			$('input[name="coupon_type"]').prop("required", true); // Make radio required
			validateCouponRequirement(); // Check if coupon is required
		  }
		} else if (selectedTemplateType === "1") { // Cart Recovery Template
		  $("#coupon_status_container").hide();
		  $("#coupon_details").hide();
		  $("#coupon_status").prop("checked", false).prop("required", false); // Remove required for coupon status
		  $('input[name="coupon_type"]').prop("checked", false).prop("required", false); // Remove required when hidden
		  $("select.coupon-dropdown").val("").prop("required", false); // Ensure it's not required when hidden
		}
	  }
	  
	  toggleTemplateTypeSections();
	  $("#template_type").on("change", toggleTemplateTypeSections);
	  
	  function toggleCouponDetails() {
		if ($("#coupon_status").is(":checked")) {
		  $("#coupon_details").show();
		  $('input[name="coupon_type"]').prop("required", true); // Make radio required when visible
		  validateCouponRequirement(); // Check if coupon is required
		} else {
		  $("#coupon_details").hide();
		  $('input[name="coupon_type"]').prop("checked", false).prop("required", false); // Remove required and uncheck when hidden
		  $("select.coupon-dropdown").val("");
		}
	  }
	  
	  toggleCouponDetails();
	  $("#coupon_status").on("change", toggleCouponDetails);
	  
	  // Handle radio button change for coupon type and toggle select dropdowns
	  $('input[name="coupon_type"]').on("change", function () {
		var selectedCouponType = $(this).val();
	  
		if (selectedCouponType === "woocommerce_coupon") {
		  $(".woocommerce_coupon_lists").show();
		  $(".flexi_coupon_lists").hide();
		  $("select[name='woocommerce_coupon_name']").prop("required", true);
		  $("select[name='flexi_coupon_name']").prop("required", false).val("");
		} else if (selectedCouponType === "flexi_dynamic_coupon") {
		  $(".flexi_coupon_lists").show();
		  $(".woocommerce_coupon_lists").hide();
		  $("select[name='flexi_coupon_name']").prop("required", true);
		  $("select[name='woocommerce_coupon_name']").prop("required", false).val("");
		}
	  
		validateCouponRequirement(); // Re-check when coupon type changes
	  });
	  
	  // Function to validate if a coupon is required when template type is 0 (Coupon Template)
	  function validateCouponRequirement() {
		var selectedTemplateType = $("#template_type").val();
		var couponStatus = $("#coupon_status").is(":checked");
		var selectedCouponType = $('input[name="coupon_type"]:checked').val();
		var woocommerceCouponSelected = $("select[name='woocommerce_coupon_name']").val();
		var flexiCouponSelected = $("select[name='flexi_coupon_name']").val();
	  
		if (selectedTemplateType === "0") { // If template type is coupon
		  if (!couponStatus) {
			$("#coupon_status").prop("required", true); // Ensure coupon status is required
		  }
	  
		  if (couponStatus) {
			if (selectedCouponType === "woocommerce_coupon" && !woocommerceCouponSelected) {
			  $("select[name='woocommerce_coupon_name']").prop("required", true);
			} else if (selectedCouponType === "flexi_dynamic_coupon" && !flexiCouponSelected) {
			  $("select[name='flexi_coupon_name']").prop("required", true);
			} else {
			  $("select.coupon-dropdown").prop("required", false); // No coupon required if not selected
			}
		  }
		}
	  }
	  
	  // Check for already selected coupon type on page load
	  function checkInitialCouponType() {
		var selectedCouponType = $('input[name="coupon_type"]:checked').val();
	  
		if (selectedCouponType === "woocommerce_coupon") {
		  $(".woocommerce_coupon_lists").show();
		  $(".flexi_coupon_lists").hide();
		  $("select[name='woocommerce_coupon_name']").prop("required", true);
		  $("select[name='flexi_coupon_name']").prop("required", false);
		} else if (selectedCouponType === "flexi_dynamic_coupon") {
		  $(".flexi_coupon_lists").show();
		  $(".woocommerce_coupon_lists").hide();
		  $("select[name='flexi_coupon_name']").prop("required", true);
		  $("select[name='woocommerce_coupon_name']").prop("required", false);
		}
		
		// Make radio buttons required if coupon details are visible
		if ($("#coupon_details").is(":visible")) {
		  $('input[name="coupon_type"]').prop("required", true);
		}
	  
		// Validate coupon requirement on initial load
		validateCouponRequirement();
	  }
	  
	  // Trigger the initial check on page load
	  checkInitialCouponType();
	  
	  
    // function toggleTemplateTypeSections() {
    //   var selectedTemplateType = $("#template_type").val();

    //   if (selectedTemplateType === "0") {
    //     $("#coupon_status_container").show();
    //     if ($("#coupon_status").is(":checked")) {
    //       $("#coupon_details").show();
    //       $("select.coupon-dropdown").prop("required", true);
    //     }
    //   } else if (selectedTemplateType === "1") {
    //     $("#coupon_status_container").hide();
    //     $("#coupon_details").hide();
    //     $("#coupon_status").prop("checked", false);
    //     $('input[name="coupon_type"]').prop("checked", false);
    //     $("select.coupon-dropdown").val("").prop("required", false); // Ensure it's not required when hidden
    //   }
    // }

    // toggleTemplateTypeSections();
    // $("#template_type").on("change", toggleTemplateTypeSections);

    // function toggleCouponDetails() {
    //   if ($("#coupon_status").is(":checked")) {
    //     $("#coupon_details").show();
    //   } else {
    //     $("#coupon_details").hide();
    //     $('input[name="coupon_type"]').prop("checked", false);
    //     $("select.coupon-dropdown").val("");
    //   }
    // }

    // toggleCouponDetails();
    // $("#coupon_status").on("change", toggleCouponDetails);

    // // Handle radio button change for coupon type and toggle select dropdowns
    // $('input[name="coupon_type"]').on("change", function () {
    //   var selectedCouponType = $(this).val();

    //   if (selectedCouponType === "woocommerce_coupon") {
    //     $(".woocommerce_coupon_lists").show();
    //     $(".flexi_coupon_lists").hide();
    //     $("select[name='woocommerce_coupon_name']").prop("required", true);
    //     $("select[name='flexi_coupon_name']").prop("required", false).val("");
    //   } else if (selectedCouponType === "flexi_dynamic_coupon") {
    //     $(".flexi_coupon_lists").show();
    //     $(".woocommerce_coupon_lists").hide();
    //     $("select[name='flexi_coupon_name']").prop("required", true);
    //     $("select[name='woocommerce_coupon_name']")
    //       .prop("required", false)
    //       .val("");
    //   }
    // });

    // reset trigger modal on close
    $(document).on("click", "#modal_close", function () {
      $("#trigger_form").trigger("reset");
    });

    // save trigger form.
    $(document).on("click", "#flexi_save_trigger", function (e) {
      e.preventDefault();
      var formData = $("#trigger_form").serialize();
      // Send the AJAX request
      $.ajax({
        url: ajaxurl,
        data: {
          dataType: "json",
          action: "flexi_save_trigger_data",
          trigger_formdata: formData,
        },
        type: "POST",
        success: function (response) {
          if (response.data) {
            $("#trigger_form").trigger("reset");
            $("#open-modal").hide();
            $("#open-edit-modal").hide();
            $(".append_notice").append(
              '<div class="notice notice-success is-dismissible"><p>' +
                response.data +
                "</p></div>"
            );
          } else {
            $(".append_notice_error").append(
              '<div class="notice notice-error is-dismissible"><p>' +
                response.data +
                "</p></div>"
            );
          }
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
        },
      });
    });

    // Coupon limit input box toggle.
    var flexi_usage_limit = $("#flexi_usage_limit");
    var flexi_coupon_limit = $("#flexi_coupon_limit");

    function toggleCouponInputBox() {
      if (flexi_usage_limit.is(":checked")) {
        flexi_coupon_limit.show();
      } else {
        flexi_coupon_limit.hide();
      }
    }
    toggleCouponInputBox();
    flexi_usage_limit.on("change", toggleCouponInputBox);
  });

  // Coupon section
  $(document).ready(function () {
    // Initialize Select2 for product and category dropdowns
    function initializeSelect2() {
      // Product Select
      $(".product-select").select2({
        ajax: {
          url: ajaxurl,
          dataType: "json",
          delay: 150,
          data: function () {
            return {
              action: "woocommerce_get_all_products", // Fetch products
            };
          },
          processResults: function (data) {
            return {
              results: data,
            };
          },
          cache: true,
        },
        placeholder: "Select products",
        multiple: true,
        allowClear: true,
      });

      // Category Select
      $(".category-select").select2({
        ajax: {
          url: ajaxurl,
          dataType: "json",
          data: function () {
            return {
              action: "woocommerce_get_all_categories", // Fetch categories
            };
          },
          processResults: function (data) {
            return {
              results: data,
            };
          },
          cache: true,
        },
        placeholder: "Select categories",
        multiple: true,
        allowClear: true,
      });
    }

    // Toggle visibility of the product or category select based on "Based On" selection
    $(document).on("change", '[name^="based-on-"]', function () {
      let basedOnValue = $(this).val();
      let filterRow = $(this).closest(".filter-row");

      // Hide both by default
      filterRow.find(".product-id-select-container").hide();
      filterRow.find(".product-category-select-container").hide();
      filterRow.find(".include-exclude-select-container").hide();

      // Show product or category fields based on the selection
      if (basedOnValue !== "") {
        filterRow.find(".include-exclude-select-container").show();
      }
      if (basedOnValue === "product_id") {
        filterRow.find(".product-id-select-container").show();
      } else if (basedOnValue === "product_category") {
        filterRow.find(".product-category-select-container").show();
      }
    });

    // Initialize Select2 when the page is ready
    initializeSelect2();

    // Add new filter row
    let filterCount = 1;
    $("#addFilter").on("click", function () {
      let lastRow = document.querySelector(".filter-row:last-of-type");
      let rowCount = 1;
      if (lastRow) {
        rowCount = parseInt(
          lastRow.querySelector('input[type="hidden"]').value
        );
      }
      let filterCount = rowCount + 1;

      filterCount++;
      let newFilterRow = `
				<div class="filter-row" id="filterRow-${filterCount}" style="display: flex; align-items: center; gap: 10px;">
					<input type="hidden" value="${filterCount}" name="filter_count-${filterCount}">
					
					<!-- Based On Select -->
					<select name="based-on-${filterCount}" class="based-on" style="width: 150px;">
						<option value="">--Select--</option>
						<option value="product_id">Product ID</option>
						<option value="product_category">Product Category</option>
					</select>
	
					<!-- Include/Exclude Select -->
					<div class="include-exclude-select-container" style="display:none">
						<select name="include_exclude-${filterCount}" style="width: 120px;">
							<option value="include">Include</option>
							<option value="exclude">Exclude</option>
						</select>
					</div>
	
					<!-- Product ID select box -->
					<div class="product-id-select-container" style="display: none;">
						<label>Select Product IDs</label>
						<select class="product-select" name="product_ids-${filterCount}[]" multiple="multiple" style="width: 200px;"></select>
					</div>
	
					<!-- Product Category select box -->
					<div class="product-category-select-container" style="display: none;">
						<label>Select Product Categories</label>
						<select class="category-select" name="category_ids-${filterCount}[]" multiple="multiple" style="width: 200px;"></select>
					</div>
					
					<!-- Remove Filter Button -->
					<button type="button" class="removeFilter" data-row-id="${filterCount}">X</button>
				</div>
			`;

      $("#filterForm").append(newFilterRow);

      // Re-initialize Select2 on the new rows
      initializeSelect2();
    });

    // Remove filter row
    $(document).on("click", ".removeFilter", function () {
      let rowId = $(this).data("row-id");
      $("#filterRow-" + rowId).remove();
    });
  });

  // rule set
  $(document).ready(function () {
    // Initialize Select2 for product, category, and role dropdowns
    function initializeSelect2() {
      // Product Select
      $(".product-select").select2({
        ajax: {
          url: ajaxurl,
          dataType: "json",
          delay: 150,
          data: function () {
            return {
              action: "woocommerce_get_all_products", // Fetch products
            };
          },
          processResults: function (data) {
            return {
              results: data,
            };
          },
          cache: true,
        },
        placeholder: "Select products",
        multiple: true,
        allowClear: true,
      });

      // Category Select
      $(".category-select").select2({
        ajax: {
          url: ajaxurl,
          dataType: "json",
          data: function () {
            return {
              action: "woocommerce_get_all_categories", // Fetch categories
            };
          },
          processResults: function (data) {
            return {
              results: data,
            };
          },
          cache: true,
        },
        placeholder: "Select categories",
        multiple: true,
        allowClear: true,
      });

      // User Roles Select
      $(".roles-select").select2({
        ajax: {
          url: ajaxurl,
          dataType: "json",
          data: function () {
            return {
              action: "woocommerce_get_all_roles", // Fetch Roles
            };
          },
          processResults: function (data) {
            return {
              results: data,
            };
          },
          cache: true,
        },
        placeholder: "Select user roles",
        multiple: true,
        allowClear: true,
      });
    }

    // Toggle visibility of input fields based on "Based On" selection
    $(document).on("change", '[name^="filter-on-"]', function () {
      let basedOnValue = $(this).val();
      let basedRow = $(this).closest(".based-row");

      // Hide all conditional fields by default
      basedRow
        .find(
          ".comparison-select-container, .include-exclude-select-container, .product-id-select-container, .product-category-select-container, .user-roles-select-container, .limit-input-container, .num-items-input-container"
        )
        .hide();

      if (basedOnValue === "num_of_items") {
        basedRow.find(".comparison-select-container").show();
        basedRow.find(".num-items-input-container").show();
      } else if (basedOnValue === "total_amt") {
        basedRow.find(".comparison-select-container").show();
        basedRow.find(".limit-input-container").show();
      } else if (basedOnValue === "user_roles") {
        basedRow.find(".include-exclude-select-container").show();
        basedRow.find(".user-roles-select-container").show();
      } else if (basedOnValue === "product_id") {
        basedRow.find(".include-exclude-select-container").show();
        basedRow.find(".product-id-select-container").show();
      } else if (basedOnValue === "product_category") {
        basedRow.find(".include-exclude-select-container").show();
        basedRow.find(".product-category-select-container").show();
      }
    });

    // Initialize Select2 when the page is ready
    initializeSelect2();

    // Add new filter row

    $("#addBasedon").on("click", function () {
      let lastRow = document.querySelector(".based-row:last-of-type");
      let rowCount = 1;
      if (lastRow) {
        rowCount = parseInt(
          lastRow.querySelector('input[type="hidden"]').value
        );
      }
      let filterCount = rowCount + 1;

      // alert(filterCount);
      let newFilterRow = `
				<div class="based-row" id="basedRow-${filterCount}" style="display: flex; align-items: center; gap: 10px; ">
					<input type="hidden" value="${filterCount}" name="based_count-${filterCount}">
					
					<!-- Based On Select -->
					<select name="filter-on-${filterCount}" class="filter-on" style="width: 150px;">
						<option value="">--Select--</option>
						<option value="num_of_items">Number Of Items</option>
						<option value="total_amt">Total Amount</option>
						<option value="user_roles">User Roles</option>
						<option value="product_id">Product ID</option>
						<option value="product_category">Product Category</option>
					</select>
	
					<!-- Comparison Select (for num_of_items and total_amt) -->
					<div class="comparison-select-container" style="display: none;">
						<select name="comparison_select-${filterCount}" style="width: 120px;">
							<option value="greater_than">Greater Than</option>
							<option value="less_than">Less Than</option>
							<option value="equal_to">Equal To</option>
							<option value="less_than_n_equals">Less Than & Equal To</option>
							<option value="greater_than_n_equals">Greater Than & Equal To</option>
						</select>
					</div>
	
					<!-- Include/Exclude Select -->
					<div class="include-exclude-select-container" style="display:none">
						<select name="include_exclude-${filterCount}" style="width: 120px;">
							<option value="include">Include</option>
							<option value="exclude">Exclude</option>
						</select>
					</div>
	
					<!-- Product ID select box -->
					<div class="product-id-select-container" style="display: none;">
						<label>Select Product IDs</label>
						<select class="product-select" name="product_ids-${filterCount}[]" multiple="multiple" style="width: 200px;"></select>
					</div>
	
					<!-- Product Category select box -->
					<div class="product-category-select-container" style="display: none;">
						<label>Select Product Categories</label>
						<select class="category-select" name="category_ids-${filterCount}[]" multiple="multiple" style="width: 200px;"></select>
					</div>
	
					<!-- User Roles select box -->
					<div class="user-roles-select-container" style="display: none;">
						<label>Select Roles</label>
						<select class="roles-select" name="roles-${filterCount}[]" multiple="multiple" style="width: 200px;"></select>
					</div>
	
					<!-- Amount Input (for total_amt) -->
					<div class="limit-input-container" style="display: none;">
						<label>Enter Amount</label>
						<input type="number" class="amount-input" name="amount-${filterCount}" style="width: 200px;">
					</div>
	
					<!-- Number of Items Input (for num_of_items) -->
					<div class="num-items-input-container" style="display: none;">
						<label>Enter Number</label>
						<input type="number" class="items-number" name="items-num-${filterCount}" style="width: 200px;" min="1" value="1">
					</div>
	
					<!-- Remove Filter Button -->
					<button type="button" class="removeFilter" data-row-id="${filterCount}">X</button>
				</div>
			`;

      $("#ruleset_filter").append(newFilterRow);

      // Re-initialize Select2 on the new rows
      initializeSelect2();
    });

    // Remove filter row
    $(document).on("click", ".removeFilter", function () {
      let rowId = $(this).data("row-id");
      $("#basedRow-" + rowId).remove();
    });
  });

  // Admin notification module
  $(document).ready(function () {
    // Initialize Select2
    $("#excel_select_col")
      .select2({
        placeholder: "Select Columns:",
        allowClear: true,
        width: "100%",
      })
      .val(
        $("#excel_select_col option")
          .map(function () {
            return this.value;
          })
          .get()
      )
      .trigger("change");

    // Update the table with selected options
    function updateSelectedColumns() {
      const selectedValues = $("#excel_select_col").val();
      const selectedColumnsBody = $("#selected-excel-columns");
      selectedColumnsBody.empty();

      // console.log(selectedValues);
      if (Array.isArray(selectedValues) && selectedValues.length > 0) {
        selectedValues.forEach((value) => {
          const columnName = $(
            "#excel_select_col option[value='" + value + "']"
          ).text();
          selectedColumnsBody.append("<th>" + columnName + "</th>");
        });
      }
    }

    $("#excel_select_col").on("change", function () {
      updateSelectedColumns();
    });

    updateSelectedColumns();

    // Function to toggle the "Set Report Days" section
    function toggleReportDays() {
      if ($("#receive_report").is(":checked")) {
        $("#toggle_diplay_admin_noti").show();
        $("#admin_report_duration").prop("required", true);
      } else {
        $("#toggle_diplay_admin_noti").hide();
        $("#admin_report_duration").prop("required", false);
      }
    }

    // Initially check the state of the checkbox on page load
    toggleReportDays();

    // Add event listener to the "Receive Report" checkbox
    $("#receive_report").on("change", function () {
      toggleReportDays();
    });

    // Toggle admin-notification related fields
    var purchaseMailCheckbox = $("#purchase_mail");
    var purchaseDurationRow = $("#toggle_diplay_purchase_mail");

    function togglePurchaseDuration() {
      if (purchaseMailCheckbox.is(":checked")) {
        purchaseDurationRow.css("display", "table-row-group");
      } else {
        purchaseDurationRow.css("display", "none");
      }
    }
    togglePurchaseDuration();
    purchaseMailCheckbox.on("change", togglePurchaseDuration);

    // send purchase_test_mail
    $(document).on("click", "#purchase_test_mail", function (e) {
      e.preventDefault();

      let send_to = $("#purchase_mail_to").val();
      let email_subject = $("#purc_email_subject").val();
      let email_body = tinymce.get("purchase_email_body").getContent();
      if (send_to === "") {
        alert("Please enter an email to send the test mail.");
        return;
      }

      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "flexi_send_test_mail",
          send_to: send_to,
          email_subject: email_subject,
          email_body: email_body,
        },
        success: function (response) {
          if (response.success) {
            alert(response.data);
          } else {
            alert(response.data);
          }
        },
      });
    });

    // send _report_over_mail to admin
    $(document).on("click", "#admin_test_mail", function (e) {
      e.preventDefault();

      let send_to = $("#admin_send_test_to").val();
      let email_subject = $("#admin_email_subject").val();
      let selected_columns = $("#excel_select_col").val();

      if (send_to == "") {
        alert("Please enter an email to send the test mail.");
        return;
      }
      // Perform AJAX request to generate Excel and send mail
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "flexi_send_report_over_mail",
          send_to: send_to,
          email_subject: email_subject,
          selected_columns: selected_columns,
        },
        success: function (response) {
          if (response.success) {
            alert(response.data);
          } else {
            alert(response.data);
          }
        },
      });
    });
  });
})(jQuery);
