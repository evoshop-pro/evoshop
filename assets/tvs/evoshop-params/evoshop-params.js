
var $ = jQuery;

function sort(){
	evo.sortable('.es-param-rows > .row', {
        complete: function() {
        	getAllEsParams();
            return true;
        }
    });
}

$(document).ready(function(){
sort();


	$('body').on('click', '.es-param-wrap .btn-add', function(){
		var nameInput = $(this).closest('.es-params').find('input[name="es-param-name"]');
		var priceInput = $(this).closest('.es-params').find('input[name="es-param-price"]');


		if(!nameInput.val()) {
			$(nameInput).addClass('has-error');
		} else {
			$(nameInput).removeClass('has-error');

			var newRow = $(this).closest('.es-params').clone();

			$(newRow).find('button.btn-add').addClass('btn-danger').removeClass('btn-success').addClass('btn-del').removeClass('btn-add');
			$(newRow).find('i.fa-plus').addClass('fa-trash').removeClass('fa-plus');
			$(newRow).appendTo($(this).closest('.es-param-wrap').find('.es-param-rows'));

			$(this).closest('.es-params').find('input').val('');
			sort();
		}

		return false;
	});

	$('body').on('click', '.es-param-wrap .btn-del', function(){
		$(this).closest('.es-params').remove();
		return false;
	});
});

function getAllEsParams() {
	var status = true;
	$('.es-param-wrap').each(function(){
		var pType = $(this).find('select[name="param-type"]').val();
		var pairs = [];
		$(this).find('.es-param-rows .es-params').each(function(i,el){
			$(el).find('input[name="es-param-name"]').removeClass('has-error');
			var pName = $(el).find('input[name="es-param-name"]').val();
			var pPrice = $(el).find('input[name="es-param-price"]').val();
			if(pName && pPrice) {
				pairs.push(pName + '==' + pPrice);
			} else if(pName) {
				pairs.push(pName);
			} else {
				$(el).find('input[name="es-param-name"]').addClass('has-error');
				status = false;
			}
		});

		if(pairs.length>0) {
			$(this).find('input[type="hidden"]').val(pType + ':' + pairs.join('||'));
			//console.log(pType + ':' + pairs.join('||'));
		}	
	});

	return status;
}

actions.save = function() {
	if(getAllEsParams()===true) {
		documentDirty = false;
		form_save = true;
		document.mutate.save.click();
	}
}