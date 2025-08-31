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
})(jQuery, window);
