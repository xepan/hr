$.each({

	doSalarySheetCalculation: function(target,employee_id,total_working_days, salaries_array){
		// target PF Salary
		//employee_id 5
		// total_working _days =30
		// salary_array =["Basic","Salary",'PF Salary',"Employee part",...,"PresentDays","PaidLEaves"]

		net_salary=0;
		$.each(salaries_array,function(index,salary_name){
			/*
				IF field exists by attr 'salary_name_employee_id'{
					$.each(salary_array){
						replace in this field expression 
							{salary_name} with $('salary_name_'employee_id).val()
					}
					// replace {TotalWorkingDays} in expression with total_working_days
					//replace 'min' with Math.min

					eval("'salary_name_employee_id'.val("+expression_string concat here+")";)
					if(field_attribute xyz =' add')
						net_value += 'salary_name_employee_id'.val();
					if(field_attribute xyz =' minus')
						net_value -= 'salary_name_employee_id'.val();
				} 


			*/
				var obj = $("."+salary_name['name']+"_"+employee_id);

				// if($(target).attr('data-employee-salary') === salary_name['name'])
				// 	console.log("salary name = "+salary_name['name']);

				if(obj.length){
				// if(obj.length && $(target).attr('data-employee-salary') != salary_name['name']){

					// continue for target field/ changed field
					// console.log("."+salary_name['name']+"_"+employee_id);
					// console.log(obj);
					var expression = obj.attr('data-xepan-salarysheet-expression');
					// console.log(expression);
					
					add_deduction = obj.attr('data-add_deduction');

					if(expression == undefined || expression == null || !expression.length){
						
						if($(target).attr('id') === $(obj).attr('id')){
							expression = $(target).val();
							// $(target).attr('data-xepan-salarysheet-expression',$(target).val());
						}else{
							expression = $(obj).val();
						}
					}
					

					$.each(salaries_array,function(index,salary){
						search_str = "{"+salary['name']+"}";
						replace_str = $('.'+salary['name']+'_'+employee_id).val();

						expression = expression.split(search_str).join(replace_str);
						// console.log(expression);
					});

					expression = expression.split("{TotalWorkingDays}").join(total_working_days);
					// console.log(expression);
					expression = expression.split("min").join("Math.min");
					// console.log(expression);
					expression = expression.split("max").join("Math.max");
					expression = expression.split("round").join("Math.round");

					// $(".'+salary_name['name']+'_'+employee_id+'").val('+expression+');');
					// $('.'+salary_name['name']+'_'+employee_id).val(expression);
					$('.'+salary_name['name']+'_'+employee_id).val(eval(expression));

					if(add_deduction === "add"){
						net_salary += parseFloat($('.'+salary_name['name']+'_'+employee_id).val());
						// console.log(net_salary);
					}

					if(add_deduction === "deduction"){
						net_salary -= parseFloat($('.'+salary_name['name']+'_'+employee_id).val());
						// console.log(net_salary);
					}

					// console.log(add_deduction);
				}

			// console.log("."+salary_name['name']+"_"+employee_id);
			// console.log(obj);
		});
		$('input.NetAmount_'+employee_id).val(net_salary);


		// selector_class =  ".xepan-row-salarysheet-"+employee_id;	
		// row_obj = $(selector_class);

		// var net_amount = 0;
		// $(row_obj).find('input.do-change-salarysheet-factor')
		// .each(function() {
			
		// 	salary = $(this).attr('data-employee-salary');
		// 	old_value = $(this).val();

		// 	amount = old_value;
		// 		// var regExp = /\{([^)]+)\}/;
		// 		// var matches = regExp.exec(expression);
		// 		//console.log(expression);
  //       		$(this).val(expression);
		// 	}

  //       	$(this).val(amount);

  //       	if(add_deduction == "add"){
  //       		net_amount = net_amount + amount;
  //       	}
  //       	if(add_deduction == "deduction"){
  //       		net_amount = net_amount - amount;
  //       	}

  //       });

		// if(net_amount > 0){
		// 	$('input.NetAmount_'+employee_id).val(net_amount);
		// }
	},
	evalPayrollRow: function(){

	}
}, $.univ._import);