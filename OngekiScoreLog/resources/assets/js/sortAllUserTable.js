var options = {
    valueNames: ['sort_id', 'sort_name', 'sort_trophy', 'sort_lv', 'sort_rating', 'sort_max', 'sort_bp', 'sort_update']
};
var sortTable = new List('sort_table', options);
sortTable.sort('sort_update', {order: "desc"});