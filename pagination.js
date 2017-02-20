$(document).ready(function() {
	$("#results").load("pager.php", afterLoad);
	$("#results").on("click", ".pagination a", function (e){
		e.preventDefault();
		$("#loading-div").show();
		var page = $(this).attr("data-page");
		$("#results").load("pager.php",{"page":page}, afterLoad, function(){
			$("#loading-div").hide();
		});
	});
	function afterLoad() {
		$(".details_btn").on("click", function(){
			var name = ($(this).attr("data-value"));
			$("#"+name).toggle();
		});
	}
});