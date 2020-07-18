$(".btn").button();

function review(g) {
        window.location.href = "./index.php?s=/InstoreQuery/doReview/ism_id/" + g
};

function sumbitIN(g) {
    window.location.href = "./index.php?s=/InstoreQuery/doSubmitIN/ism_id/" + g
};

function rollbackIN(g) {
    window.location.href = "./index.php?s=/InstoreQuery/doRollbackIN/ism_id/" + g
};

function editIN(g) {
    window.location.href = "./index.php?s=/InstoreQuery/toEdit/ism_id/" + g
};