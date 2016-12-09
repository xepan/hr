$.each({

	doSalarySheetCalculation: function(target,employee_id){

		selector_class =  ".xepan-row-salarysheet-"+employee_id;	
		row_obj = $(selector_class);

		var net_amount = 0;
		$(row_obj).find('input.do-change-salarysheet-factor')
		.each(function() {
			
			expression = $(this).attr('data-xepan-salarysheet-expression');
			add_deduction = $(this).attr('data-add_deduction');
			salary = $(this).attr('data-employee-salary');
			old_value = $(this).val();

			amount = old_value;
			if(expression != undefined || expression != null || !expression){
				// var regExp = /\{([^)]+)\}/;
				// var matches = regExp.exec(expression);
				// console.log(matches);

			}

        	$(this).val(amount);

        	if(add_deduction == "add"){
        		net_amount = net_amount + amount;
        	}
        	if(add_deduction == "deduction"){
        		net_amount = net_amount - amount;
        	}

        });

		if(net_amount > 0){
			$('input.NetAmount_'+employee_id).val(net_amount);
		}
	}
}, $.univ._import);