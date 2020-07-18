
function toSearch() {
    $("#dialog").dialog({height: 400, width: 650, modal: true, buttons: {"确认": function () {
        var g = $("#dialog").contents();
        var i = g.find("input[name='ism_sellerunit']").val();
        var m = g.find("select[name='iss_quality']").val();
        var k = g.find("input[name='iss_store']").val();
        var o = g.find("input[name='ism_date_start']").val();
        var p = g.find("input[name='ism_date_end']").val();
        var q = g.find("input[name='iss_prodname']").val();
        var r = g.find("input[name='ism_status_time']").val();
        action_url = "./index.php?s=/StoreCount/search";
        if (i != '请输入关键字或空格')action_url += "/ism_sellerunit/" + i;
        if (m != "")action_url += "/iss_quality/" + m;
        if (k != "")action_url += "/iss_store/" + k;
        if (o != "")action_url += "/ism_date_start/" + o;
        if (p != "")action_url += "/ism_date_end/" + p;
        if (r != "")action_url += "/ism_status_time/" + r;
        if (q != '请输入关键字或空格')action_url += "/iss_prodname/" + encodeURIComponent(q);
        window.location.href = encodeURI(action_url)
    }, '取消': function () {
        $(this).dialog("close")
    }}})
};
$("#ism_date_start").datepicker();
$("#ism_date_end").datepicker();
$("#ism_status_time").datepicker();
$.widget("custom.catcomplete", $.ui.autocomplete, {_renderMenu: function (g, h) {
    var i = this, j = "";
    $.each(h, function (k, l) {
        if (l.category != j) {
            g.append("<li class='ui-autocomplete-category'>" + l.category + "</li>");
            j = l.category
        }
        ;
        i._renderItem(g, l)
    })
}});
function clearTip(g) {
    if ($(g).val() == '请输入关键字或空格') {
        $(g).attr('style', 'color:#000');
        $(g).val('')
    }
};
function fillTip(g) {
    if ($(g).val() == '') {
        $(g).attr('style', 'color:#CCC');
        $(g).val('请输入关键字或空格')
    }
};
function bindAutoComplete() {
    $("#iss_prodname").catcomplete({source: './index.php?s=/InstoreBook/getProduct', minLength: 1, delay: 0, select: function (g, h) {
        $('#iss_prodname').val(h.item.prod_name)
    }})
};
bindAutoComplete();
$(".btn").button();

function bindAutoComplete22() {
    $("#ism_sellerunit").catcomplete22({source: './index.php?s=/InstoreBook/autoSelect22', minLength: 1, delay: 0, select: function (g, h) {
        $('#ism_sellerunit').val(h.item.gust_name)
    }})
};
$.widget("custom.catcomplete22", $.ui.autocomplete, {_renderMenu: function (g, h) {
    var i = this, j = "";
    $.each(h, function (k, l) {
        i._renderItem(g, l)
    })
}});
bindAutoComplete22();

function bindQuality() {
    $.getJSON('./index.php?s=/InstoreBook/getQuality', function (h) {
        $.each(h, function (i, j) {
            $('#iss_quality').append("<option value='" + j.pdqu_name + "'>" + j.pdqu_name + "</option>")
        })
    })
};
bindQuality();