
    //页面上点击此属性，将当前页的列表数据全部选中
    $("#checkAll").click(function(){
    //判断当前点击的复选框处于什么状态$(this).is(":checked") 返回的是布尔类型
    if($(this).is(":checked")){
        $("input[name='ids[]']").prop("checked",true);
    }else{
        $("input[name='ids[]']").prop("checked",false);
        }
    });

function checkForm(formObj){
    var rs = true;
    formObj.find('input').each(function (){
        if($(this).attr('required') != undefined){
            if($(this).val() == ''){
                var msg = $(this).closest('.form-group').find('label').text();
                // msg = msg.substring(0, msg.length -1);
                swal({
                    position: 'center',
                    type: 'error',
                    title: '请填写'+msg,
                    showConfirmButton: false,
                    toast:false,
                    timer: 1000
                });
                rs = false;
                return false;
            }
        }
    });
    return rs;
}