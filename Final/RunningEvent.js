function bulkAction(phase) {
    let lstRequest = [];
    $('[refQ]').each((index, element) => {
        lstRequest.push($(element).attr('refQ'));
    });
    if(lstRequest.length > 0) {
        window.location.href='?Event[]=' + lstRequest.join('&Event[]=');
    }
}