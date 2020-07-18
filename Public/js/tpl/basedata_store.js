function del(g) {
    if (confirm("确认删除吗？")) {
        window.location.href = "./index.php?s=/BaseData/deleteStore/sto_id/" + g
    }
};
$("#jump").change(function () {
    var g = "";
    switch ($(this).val()) {
        case"0":
            g = "./index.php?s=/BaseData/cate";
            break;
        case"1":
            g = "./index.php?s=/BaseData/index";
            break;
        case"2":
            g = "./index.php?s=/BaseData/store";
            break;
        case"3":
            g = "./index.php?s=/BaseData/guest";
            break;
        case"4":
            g = "./index.php?s=/BaseData/quality";
            break;
        case"5":
            g = "./index.php?s=/BaseData/carry";
            break
    }
    ;
    window.location.href = g
});

function toAddStore() {
    $("#dialog").dialog(
        {height: 400, width: 650, title: '仓库添加', modal: true,
            buttons: {"保存": function () {
                var g = $("#sto_name").val();
                var h = $("#sto_address").val();
                var i = $("#sto_storer").val();
                var j = $("#sto_phone").val();
                action_url = "./index.php?s=/BaseData/doAddStore";
                if (g != "")
                    action_url += "/sto_name/" + g;
                if (h != "")action_url += "/sto_address/" + h;
                if (i != "")action_url += "/sto_storer/" + i;
                if (j != "")action_url += "/sto_phone/" + j;
                window.location.href = action_url
            }, '取消': function () {
                $(this).dialog("close")
            }}})
};
function toEditStore(g) {
    $("#dialog").dialog({height: 400, width: 650, title: '仓库编辑', modal: true, open: function () {
        $.getJSON("./index.php?s=/BaseData/getStoreById/sto_id/" + g, function (h) {
            $('#sto_name').val(h.sto_name);
            $('#sto_address').val(h.sto_address);
            $('#sto_storer').val(h.sto_storer);
            $('#sto_phone').val(h.sto_phone)
        })
    }, buttons: {"保存": function () {
        var h = $("#sto_name").val();
        var i = $("#sto_address").val();
        var j = $("#sto_storer").val();
        var k = $("#sto_phone").val();
        action_url = "./index.php?s=/BaseData/doEditStore/sto_id/" + g;
        if (h != "")action_url += "/sto_name/" + h;
        if (i != "")action_url += "/sto_address/" + i;
        if (j != "")action_url += "/sto_storer/" + j;
        if (k != "")action_url += "/sto_phone/" + k;
        window.location.href = action_url
    }, '取消': function () {
        $(this).dialog("close")
    }}})
};



$(".btn").button();