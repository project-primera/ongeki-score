var options = {
    valueNames: ['sort_title', 'sort_difficulty', 'sort_level', 'sort_extra_level']
};
var sortTable = new List('sort_table', options);
sortTable.sort('sort_extra_level', {order: "desc"});