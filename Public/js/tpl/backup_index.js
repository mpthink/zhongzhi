function resume(g) {
    if (confirm("确认恢复吗？确认后数据库也将恢复到该备份之前")) {
        window.location.href = "./index.php?s=/Backup/resume/back_id/" + g
    }
};
function del(g) {
    if (confirm("确认删除吗？删除后不可恢复")) {
        window.location.href = "./index.php?s=/Backup/delete/back_id/" + g
    }
};
$(".btn").button();