(function ($, window) {
	$(".lv_faq_item_question").on("click", function () {
		let $this = $(this);
		let $answer = $this.next(".lv_faq_item_answer");

		// Đóng tất cả trước
		$(".lv_faq_item_answer").slideUp();
		$(".lv_faq_item_question").removeClass("lv_faq_item_question_active");

		// Nếu chưa mở thì mở cái hiện tại
		if (!$answer.is(":visible")) {
			$answer.slideDown();
			$this.addClass("lv_faq_item_question_active");
		}
	});

	// click vào item có submenu
	$(".menu_mobile_item.has-sub > .menu_mobile_link").on("click", function (e) {
		e.preventDefault();

		var parent = $(this).closest(".menu_mobile_item");

		// đóng tất cả submenu khác
		$(".menu_mobile_item").not(parent).removeClass("active").find(".submenu_list").hide();

		// toggle submenu hiện tại
		parent.toggleClass("active");
		parent.find(".submenu_list").toggle();

		e.stopPropagation(); // chặn sự kiện lan ra ngoài
	});

	// click bất kỳ chỗ nào ngoài menu_mobile -> đóng hết
	$(document).on("click", function (e) {
		if (!$(e.target).closest(".menu_mobile").length) {
			$(".menu_mobile_item").removeClass("active").find(".submenu_list").hide();
		}
	});

	/* ===== Tabs ===== */
	$(".lv_tabs .lv_tabs_link").click(function (e) {
		e.preventDefault();
		var $tabs = $(this).closest(".lv_tabs");
		var target = $(this).data("tab");

		// reset active link
		$tabs.find(".lv_tabs_link").removeClass("lv_tabs_link_active");
		$(this).addClass("lv_tabs_link_active");

		// reset active panel
		$tabs.find(".lv_tabs_panel").removeClass("lv_tabs_panel_active");
		$tabs.find("#" + target).addClass("lv_tabs_panel_active");
	});

	$(".lv_latest_posts_slider").slick({
		slidesToShow: 3,
		slidesToScroll: 1,
		arrows: true,
		dots: false,
		prevArrow: '<button type="button" class="lv-arrow lv-prev"></button>',
		nextArrow: '<button type="button" class="lv-arrow lv-next"></button>',
		responsive: [
			{ breakpoint: 850, settings: { slidesToShow: 2 } },
			{ breakpoint: 768, settings: { slidesToShow: 1 } },
		],
	});

	$(".lv_partner").slick({
		dots: false, // Không hiển thị các chấm điều hướng
		infinite: true, // Lặp lại vô tận
		arrows: false, // Không hiển thị các mũi tên điều hướng
		variableWidth: true, // Mỗi slide có chiều rộng linh hoạt
		slidesToScroll: 1, // Di chuyển 1 slide mỗi lần
		autoplay: true, // Tự động chạy
		autoplaySpeed: 0, // Tốc độ chuyển slide gần như ngay lập tức (0ms)
		cssEase: "linear", // Sử dụng hiệu ứng mượt mà (linear)
		speed: 5000, // Tốc độ chuyển động của từng slide (5000ms = 5 giây cho mỗi slide)
		pauseOnHover: false, // Không tạm dừng khi hover
		pauseOnFocus: false, // Không tạm dừng khi focus
	});
})(jQuery, window);
