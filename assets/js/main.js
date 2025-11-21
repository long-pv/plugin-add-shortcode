(function ($, window) {
	var prefix = "on8kbet23_f19ac0d2"; // 👉 CHỈ SỬA Ở ĐÂY ĐỂ ĐỔI PREFIX

	$("." + prefix + "_faq_item_question").on("click", function () {
		let $this = $(this);
		let $answer = $this.next("." + prefix + "_faq_item_answer");

		$("." + prefix + "_faq_item_answer").slideUp();
		$("." + prefix + "_faq_item_question").removeClass(prefix + "_faq_item_question_active");

		if (!$answer.is(":visible")) {
			$answer.slideDown();
			$this.addClass(prefix + "_faq_item_question_active");
		}
	});

	$("." + prefix + "_menu_mobile_item.has-sub > ." + prefix + "_menu_mobile_link").on("click", function (e) {
		e.preventDefault();

		var parent = $(this).closest("." + prefix + "_menu_mobile_item");

		$("." + prefix + "_menu_mobile_item")
			.not(parent)
			.removeClass("active")
			.find("." + prefix + "_submenu_list")
			.hide();

		parent.toggleClass("active");
		parent.find("." + prefix + "_submenu_list").toggle();

		e.stopPropagation();
	});

	$(document).on("click", function (e) {
		if (!$(e.target).closest("." + prefix + "_menu_mobile").length) {
			$("." + prefix + "_menu_mobile_item")
				.removeClass("active")
				.find("." + prefix + "_submenu_list")
				.hide();
		}
	});

	$("." + prefix + "_tabs ." + prefix + "_tabs_link").click(function (e) {
		e.preventDefault();
		var $tabs = $(this).closest("." + prefix + "_tabs");
		var target = $(this).data("tab");

		$tabs.find("." + prefix + "_tabs_link").removeClass(prefix + "_tabs_link_active");
		$(this).addClass(prefix + "_tabs_link_active");

		$tabs.find("." + prefix + "_tabs_panel").removeClass(prefix + "_tabs_panel_active");
		$tabs.find("#" + target).addClass(prefix + "_tabs_panel_active");
	});

	$("." + prefix + "_latest_posts_slider").slick({
		slidesToShow: 3,
		slidesToScroll: 1,
		arrows: true,
		dots: false,
		prevArrow: '<button type="button" class="' + prefix + "-arrow " + prefix + '-prev"></button>',
		nextArrow: '<button type="button" class="' + prefix + "-arrow " + prefix + '-next"></button>',
		responsive: [
			{ breakpoint: 850, settings: { slidesToShow: 2 } },
			{ breakpoint: 768, settings: { slidesToShow: 1 } },
		],
	});

	$("." + prefix + "_partner").slick({
		dots: false,
		infinite: true,
		arrows: false,
		variableWidth: true,
		slidesToScroll: 1,
		autoplay: true,
		autoplaySpeed: 0,
		cssEase: "linear",
		speed: 5000,
		pauseOnHover: false,
		pauseOnFocus: false,
	});

	function getTimeUntilNextEvent() {
		var now = new Date();
		var targetTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 18, 30, 0);

		if (now > targetTime) {
			targetTime.setDate(targetTime.getDate() + 1);
		}
		return targetTime - now;
	}

	function pad(n) {
		return (n < 10 ? "0" : "") + n;
	}

	function updateTimer() {
		var timeLeft = getTimeUntilNextEvent();

		var hours = Math.floor(timeLeft / (1000 * 60 * 60));
		var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
		var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

		$("#timer-hours").text(pad(hours));
		$("#timer-minutes").text(pad(minutes));
		$("#timer-seconds").text(pad(seconds));
	}

	updateTimer();
	setInterval(updateTimer, 1000);

	$("." + prefix + "_catSlider").slick({
		infinite: true,
		slidesToShow: 3,
		slidesToScroll: 1,
		centerMode: true,
		centerPadding: "0",
		focusOnSelect: true,
		autoplay: true,
		autoplaySpeed: 3000,
		arrows: false,
		dots: false,
		responsive: [
			{
				breakpoint: 768,
				settings: {
					slidesToShow: 1.5,
					slidesToScroll: 1,
					centerMode: false,
					centerPadding: "0",
					autoplay: true,
					autoplaySpeed: 3000,
					arrows: false,
					dots: false,
					infinite: false,
				},
			},
		],
	});

	$("." + prefix + "_tabs_navItem").click(function () {
		var tabId = $(this).attr("data-tab");

		$("." + prefix + "_tabs_navItem").removeClass("active");
		$(this).addClass("active");

		$("." + prefix + "_tabs_cat_panel").removeClass("active");
		$("#" + tabId).addClass("active");
	});
})(jQuery, window);
