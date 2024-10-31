var slyrmc_base_url = '',
    slyrmc_service_url = 'ajax_hook.php',
    list_exclusion = [],
    slyrmc_last_id_category = 0,
    slyrmc_cache = [],
    slyrmc_field_cat_id = '',
    slyrmc_field_cat_parent_id = '',
    slyrmc_field_prd_id = '',
    slyrmc_info = 0;

list_exclusion.push('id');
list_exclusion.push('parent_id');
list_exclusion.push('id_product_types');
list_exclusion.push('id_catalogue');
list_exclusion.push('sl_product_name');
list_exclusion.push('sl_product_image');

function slyrmc_trim(a) {
    return a.replace(/^\s+|\s+$/g, '');
}

function slyrmc_preload_show() {
    $('#gnrl_ldng').show();
}

function slyrmc_preload_hide() {
    $('#gnrl_ldng').hide();
}

function slyrmc_load_fast_menu() {

    $.ajax({
        type: "post",
        url: sl_ajax_object.ajax_url,
        data: {
            action:'slyrmc_catalog_control',
            endpoint: "menu",
            web_url: slyrmc_page_home_url,
            _ajax_nonce: sl_ajax_object.nonce
        }
    }).done(function (result) {
        slyrmc_paint_fast_menu(result, 'collapse_catalog_1');
    });
}

function slyrmc_paint_fast_menu(fastMenuArray, divElem) {

    $.each(fastMenuArray, function (key, menuObj) {
        if (menuObj.hasOwnProperty('submenu')) {

            var liAdd = '<li class="accordion-group" id="li_category_';
            liAdd = liAdd.concat(menuObj[slyrmc_field_cat_id]);
            liAdd = liAdd.concat('"><a class="accordion-toggle collapsed"  ');
            var href = "#collapse_categories_" + menuObj[slyrmc_field_cat_id];
            if (typeof menuObj.category_url !== 'undefined') {
                href = slyrmc_html_entities(menuObj.category_url);
            }
            liAdd = liAdd.concat('data-parent="#menu_pr" href="' + href);
            liAdd = liAdd.concat('" title="' + menuObj.section_name + '" data-original-title="Categorie_');
            liAdd = liAdd.concat(menuObj[slyrmc_field_cat_id]);
            liAdd = liAdd.concat('" onclick="slyrmc_load_catalog(');
            liAdd = liAdd.concat(menuObj[slyrmc_field_cat_id]);
            liAdd = liAdd.concat('); return false;"><em>');
            liAdd = liAdd.concat(menuObj.section_name);
            liAdd = liAdd.concat('</em></a>');
            liAdd = liAdd.concat('</li>');
            $("#" + divElem).append(liAdd);

            var ulAdd = '<ul id="collapse_categories_';
            ulAdd = ulAdd.concat(menuObj[slyrmc_field_cat_id]);
            ulAdd = ulAdd.concat('" class="accordion-body collapse in" style="height: auto;"></ul>');
            $("#li_category_" + menuObj[slyrmc_field_cat_id]).append(ulAdd);

            slyrmc_paint_fast_menu(menuObj.submenu, 'collapse_categories_' + menuObj[slyrmc_field_cat_id]);
        } else {
            var liAdd = '<li class="accordion-group">';
            var href = "#";
            if (typeof menuObj.category_url !== 'undefined') {
                href = slyrmc_html_entities(menuObj.category_url);
            }
            liAdd = liAdd.concat('<a href="' + href + '" onclick="slyrmc_load_catalog(');
            liAdd = liAdd.concat(menuObj[slyrmc_field_cat_id]);
            liAdd = liAdd.concat('); return false;"><em>');
            liAdd = liAdd.concat(menuObj.section_name);
            liAdd = liAdd.concat('</em></a></li>');
            $("#" + divElem).append(liAdd);
        }
    });
}

function slyrmc_load_catalog(idcategory, force_load = true) {

    idcategory = idcategory || 0;

    if (typeof (slyrmc_cache['cat']) == 'undefined') slyrmc_cache['cat'] = [];
    if (typeof (slyrmc_cache['cat'][idcategory]) == 'undefined' || force_load) {

        slyrmc_preload_show();

        $.ajax({
            type: "post",
            url: sl_ajax_object.ajax_url,
            data: {action:'slyrmc_catalog_control',
                endpoint: "catalog",
                id: idcategory,
                web_url: slyrmc_page_home_url,
                _ajax_nonce: sl_ajax_object.nonce}
        })
            .done(function (result) {

                if (result == 0) {

                    slyrmc_load_catalog(0);

                } else {

                    slyrmc_cache['cat'][idcategory] = result;
                    slyrmc_update_url('categories', idcategory);
                    slyrmc_paint_catalog(result);
                    slyrmc_preload_hide();

                }

            });

    } else {

        slyrmc_paint_catalog(slyrmc_cache['cat'][idcategory]);
        slyrmc_update_url('categories', idcategory);

    }

}

function slyrmc_update_url(type = 'categories', item_id) {

    var url_to_update = '';

    if (item_id == 0) {

        url_to_update = slyrmc_page_home_url;

    } else {

        var cache_idx = 'cat';
        var indexes = [type];

        if (type == 'products') {

            cache_idx = 'prd'

        } else {

            indexes.push("breadcrumb");

        }

        if (type == 'categories') {

            if (slyrmc_cache[cache_idx][item_id] && Object.keys(slyrmc_cache[cache_idx][item_id]).length > 0) {

                indexes.forEach(function (index) {

                    if (url_to_update == '') {

                        if (slyrmc_cache[cache_idx][item_id][index] && slyrmc_cache[cache_idx][item_id][index].length > 0) {

                            var itemArray = slyrmc_cache[cache_idx][item_id][index];

                            for (var i = 0; i < slyrmc_cache[cache_idx][item_id][index].length; i++) {

                                var itemObj = itemArray[i];
                                if (itemObj[slyrmc_field_cat_id] == item_id) {

                                    if (typeof itemObj.category_url !== 'undefined') {

                                        url_to_update = itemObj.category_url;

                                    }

                                    break;

                                }

                            }

                        }

                    }

                });

            }

        } else {

            if (slyrmc_cache[cache_idx][item_id]) {

                var itemArray = slyrmc_cache[cache_idx][item_id]['products'];

                for (var i = 0; i < slyrmc_cache[cache_idx][item_id]['products'].length; i++) {

                    var itemObj = itemArray[i];

                    if (itemObj["orig_ID"] == item_id) {

                        if (itemObj["product_url"]) {

                            url_to_update = itemObj["product_url"];

                        }

                    }

                }

            }

        }

    }

    if (url_to_update != '') {

        window.history.pushState({}, null, slyrmc_html_entities(url_to_update));

    }

}

function slyrmc_scroll_top() {

    var t = $('#slyr_catalogue').parent().position(true).top,
        o = $('html').scrollTop();

    if (o > t) $('html').scrollTop(t);

}

function slyrmc_html_entities(encodedString) {
    var textArea = document.createElement('textarea');
    textArea.innerHTML = encodedString;
    return textArea.value;
}

function slyrmc_paint_catalog(catalog) {

    var no_elements = true;

    $("#site-canvas-product").hide(0);
    $("#div_category").show(0);

    $("#div_category").empty();
    $("#breadcrumb_ul").empty();

    slyrmc_paint_breadcrumb(catalog.breadcrumb);

    if (catalog.categories && catalog.categories.length > 0) {
        no_elements = false;
        var categoryArray = catalog.categories;
        for (var i = 0; i < categoryArray.length; i++) {
            var categoryObj = categoryArray[i];
            var href = "#";
            if (typeof categoryObj.category_url !== 'undefined') {
                href = slyrmc_html_entities(categoryObj.category_url);
            }

            var divAdd = '<div class="box_elm not_thum">';
            divAdd = divAdd.concat('<div class="box_img img_on">');
            divAdd = divAdd.concat('<a href="' + href + '" onclick="slyrmc_load_catalog(');
            divAdd = divAdd.concat(categoryObj[slyrmc_field_cat_id]);
            divAdd = divAdd.concat('); return false;">');

            if (categoryObj.hasOwnProperty('section_image') && categoryObj.section_image != '' && categoryObj.section_image != null) {
                divAdd = divAdd.concat('<img alt="'+categoryObj.section_name+'" src="');
                divAdd = divAdd.concat(categoryObj.section_image);
                divAdd = divAdd.concat('">');
            } else {
                divAdd = divAdd.concat('<img alt="" src="' + slyrmc_base_url + 'images/placeholder.gif">');
            }

            divAdd = divAdd.concat('</a></div><div class="box_inf">');
            divAdd = divAdd.concat('<h7><a class="section" title="'+categoryObj.section_name+'" href="' + href + '" onclick="slyrmc_load_catalog(');
            divAdd = divAdd.concat(categoryObj[slyrmc_field_cat_id]);
            divAdd = divAdd.concat('); return false;">');
            divAdd = divAdd.concat(categoryObj.section_name);
            divAdd = divAdd.concat('</a></h7></div></div>');
            $("#div_category").append(divAdd);
        }
        $("#dropdown_ul").empty();
    }
    if (catalog.products && catalog.products.length > 0) {

        no_elements = false;
        var productArray = catalog.products;
        for (var i = 0; i < productArray.length; i++) {
            var productObj = productArray[i];
            var href = "#";
            if (typeof productObj.product_url !== 'undefined') {
                href = slyrmc_html_entities(productObj.product_url);
            }

            var divAdd = '<div class="box_elm not_thum ">';
            divAdd = divAdd.concat('<div class="box_img img_on">');
            divAdd = divAdd.concat('<a href="' + href + '" alt="'+productObj.product_name+'" onclick="slyrmc_load_product(');
            divAdd = divAdd.concat(productObj[slyrmc_field_prd_id]);
            divAdd = divAdd.concat('); return false;">');

            if (productObj.hasOwnProperty('product_image') && productObj.product_image != "") {
                divAdd = divAdd.concat('<img alt="'+productObj.product_name+'" src="');
                divAdd = divAdd.concat(productObj.product_image);
                divAdd = divAdd.concat('">');
            } else {
                divAdd = divAdd.concat('<img alt="" src="' + slyrmc_base_url + 'images/placeholder.gif">');
            }

            divAdd = divAdd.concat('</a></div><div class="box_inf">');
            divAdd = divAdd.concat('<h7><a title="'+productObj.product_name+'" href="' + href + '" class="product" onclick="slyrmc_load_product(');
            divAdd = divAdd.concat(productObj[slyrmc_field_prd_id]);
            divAdd = divAdd.concat('); return false;">');
            if (productObj.product_name) {
                divAdd = divAdd.concat(productObj.product_name);
            } else {
                divAdd = divAdd.concat("Product undefined");
            }
            divAdd = divAdd.concat('</a></h7></div></div>');
            $("#div_category").append(divAdd);
        }

        last_crumb = (catalog.breadcrumb != null) ? catalog.breadcrumb.length - 1 : 0;
        slyrmc_last_id_category = (last_crumb >= 0 && typeof (catalog.breadcrumb[last_crumb]) != 'undefined') ?
            ((catalog.breadcrumb != null) ? catalog.breadcrumb[last_crumb][slyrmc_field_cat_id] : 0) : 0;
    }

    if (no_elements) {
        var divAdd = '<div class="message">';
        ivAdd = divAdd.concat('<h5>There are no products inside this category.</h5>');
        divAdd = divAdd.concat('</div>');
        $("#div_category").append(divAdd);
        $("#dropdown_ul").empty();
    }

    slyrmc_scroll_top();
}

function slyrmc_paint_breadcrumb(breadcrumbArray) {

    var liAdd = '<li><a href="#" onclick="slyrmc_load_catalog(0); return false;">Start</a></li>';
    $("#breadcrumb_ul").append(liAdd);

    if (breadcrumbArray != null) {
        var last = breadcrumbArray.length - 1;

        for (var i = 0; i < breadcrumbArray.length; i++) {

            var breadcrumb = breadcrumbArray[i];
            var liAdd = '';
            var href = "#";
            if (typeof breadcrumb.category_url !== 'undefined') {
                href = slyrmc_html_entities(breadcrumb.category_url);
            }

            //si es el ultimo elemento...
            if (i == last) {
                liAdd = liAdd.concat('<li class="active"><a href="' + href + '" onclick="slyrmc_load_catalog(');
            } else {
                liAdd = liAdd.concat('<li><a href="' + href + '" onclick="slyrmc_load_catalog(');
            }
            liAdd = liAdd.concat(breadcrumb[slyrmc_field_cat_id]);
            liAdd = liAdd.concat('); return false;">');
            liAdd = liAdd.concat(breadcrumb.section_name);
            liAdd = liAdd.concat('</a></li>');

            $("#breadcrumb_ul").append(liAdd);
        }
    }
}

function slyrmc_load_product(idproduct) {

    if (typeof (slyrmc_cache['prd']) == 'undefined') {

        slyrmc_cache['prd'] = [];

    }

    if (typeof (slyrmc_cache['prd'][idproduct]) == 'undefined') {

        slyrmc_preload_show();

        $.ajax({
            type: 'post',
            url: sl_ajax_object.ajax_url,
            data: {
                action:'slyrmc_catalog_control',
                endpoint: 'products',
                id: idproduct,
                web_url: slyrmc_page_home_url,
                _ajax_nonce: sl_ajax_object.nonce}
        })
            .done(function (result) {

                if (result == 0) {

                    slyrmc_load_catalog(0);

                } else {

                    slyrmc_cache['prd'][idproduct] = result;
                    slyrmc_update_url('products', idproduct);
                    slyrmc_paint_product(result);
                    slyrmc_preload_hide();

                }

            });

    } else {

        slyrmc_paint_product(slyrmc_cache['prd'][idproduct]);
        slyrmc_update_url('products', idproduct);

    }

}

function slyrmc_back_catalog(catalog_id) {

    slyrmc_load_catalog(catalog_id);
    $('#site-canvas-product').hide(0);
    $('#div_category').show(0);

}

function slyrmc_paint_product(product) {

    $("#breadcrumb_ul").empty();
    slyrmc_paint_breadcrumb(product.breadcrumb);

    if (product.products.length) {

        $('#site-canvas-product').show(0);
        $('#div_category').hide(0);

        $('#gallery').unbind().empty();

        $('<div>', {id: 'div_image_preview', class: 'image-preview'}).appendTo('#gallery');

        $.each(product.products, function (key, options) {

            if (!options.hasOwnProperty('product_description') || options.product_description == '' || options.product_description == null) {

                options.product_description = '';

            }

            if (options.product_description.length && !options.product_description.match(/<\w+[^>]*>/g)) {

                options.product_description = options.product_description.replace(/(\n\r|\r\n|\n)/, '<br>');
            }

            $('#h_product_name').empty().append(options.product_name);
            $('#p_product_description').empty().append(options.product_description);
            $('#p_characteristics').empty().append(options.characteristics);
            $('#p_formats').empty().append(options.formats);
            $('#backcatalog').attr("onclick", "slyrmc_back_catalog('" + options[slyrmc_field_cat_id] + "'); return false;");

            if (options.hasOwnProperty('product_image') && options.product_image != '' && options.product_image != null) {

                var img_fmt = options.IMG_FMT, kbase = '';

                $.each(options.product_image, function (k, images) {

                    if (!kbase) kbase = k;

                    if (typeof (images[img_fmt]) != 'undefined' && images[img_fmt] != '') {

                        var objShadowbox = $('<a>', {id: 'apreview', rel: 'shadowbox', href: images[img_fmt]});

                        objShadowbox.append($('<img>', {id: 'preview', class: 'vw_detl', src: images.THM , alt:options.product_name}));
                        $('#div_image_preview').append(objShadowbox);

                    } else {

                        $('#div_image_preview').append($('<img>', {id: 'preview', src: images.THM, alt:options.product_name}));
                    }

                    $('<ul>', {id: 'carousel', class: 'slide-list'}).appendTo('#gallery');

                    return false;
                });

                imgs = 0;
                for (i in options.product_image) {
                    if (options.product_image.hasOwnProperty(i)) imgs++;
                }

                if (imgs > 1) {

                    $('#carousel').unbind();
                    var imo = 1;

                    $.each(options.product_image, function (k, imgs) {

                        $('#carousel').append('<li  class="imo' + imo + (k == kbase ? ' current' : '') + '"><a href="#" onclick="return slyrmc_change_image(' +
                            imo + ',\'' + imgs.THM + '\',\'' + imgs[img_fmt] + '\')"><img src="' + imgs.TH + '"/></a></li>');

                        imo++;
                    });
                }

            } else {

                $('#div_image_preview').append($('<img>', {id: 'preview', src: (slyrmc_base_url + 'images/placeholder.gif'), alt:options.product_name}));
                $('<ul>', {id: 'carousel', class: 'elastislide-list'}).appendTo('#gallery');
            }

        });

        Shadowbox.clearCache();
        Shadowbox.setup();

        slyrmc_scroll_top();

    }

}

function slyrmc_change_image(k, ih, ib) {

    $('#preview').attr('src', ih);
    $('#apreview').attr('href', ib);

    $('#carousel li').removeClass('current');

    $('#carousel .imo' + k).addClass('current');

    Shadowbox.clearCache();
    Shadowbox.setup();

    return false;
}

function slyrmc_check_slyr_page_params() {

    var exploded_pathname = window.location.pathname.split('/');
    var url_has_item = false;
    var item_id;
    if (exploded_pathname.length > 0) {

        if (exploded_pathname[exploded_pathname.length - 2] == slyrmc_service_url) {

            if (typeof exploded_pathname[exploded_pathname.length - 1] !== 'undefined' && exploded_pathname[3] != '') {

                var type = exploded_pathname[exploded_pathname.length - 1].substr(0, 1);
                if (type == 'c') {
                    url_has_item = true;
                    item_id = exploded_pathname[exploded_pathname.length - 1].substr(1, exploded_pathname[exploded_pathname.length - 1].length);
                    slyrmc_load_catalog(item_id);

                } else if (type == 'p') {

                    url_has_item = true;
                    item_id = exploded_pathname[exploded_pathname.length - 1].substr(1, exploded_pathname[exploded_pathname.length - 1].length);
                    slyrmc_load_product(item_id);

                }

            }

        }

    }

    if (!url_has_item) {

        slyrmc_load_catalog(0);

    }

}

function slyrmc_load_vars() {

    if (typeof plugins_url !== 'undefined' && typeof slyrmc_plugin_name_dir !== 'undefined') {

        slyrmc_base_url = plugins_url + '/' + slyrmc_plugin_name_dir + '/';

        $('.slyr_cat_prld').css('background', 'url(' + slyrmc_base_url + 'images/loading.gif) center center no-repeat');

    }

    if (typeof slyrmc_field_cat_id === 'undefined' || slyrmc_field_cat_id === '' || typeof slyrmc_field_cat_parent_id === 'undefined' || slyrmc_field_cat_parent_id === '' || typeof slyrmc_field_prd_id === 'undefined' || field_prd_id === '') {
        
        $.ajax({
            type: 'post',
            url:sl_ajax_object.ajax_url,
            data: {
                action:'slyrmc_catalog_control',
                endpoint: 'tables_fields_ids',
                _ajax_nonce: sl_ajax_object.nonce
            }
        }).done(function (tables_fields_ids) {
            slyrmc_field_cat_id = tables_fields_ids.field_cat_id;
            slyrmc_field_cat_parent_id = tables_fields_ids.field_cat_parent_id;
            slyrmc_field_prd_id = tables_fields_ids.field_prd_id;
            slyrmc_load_content();
        });

    }

}

function slyrmc_load_content() {
    
    if (typeof sl_ajax_object.ajax_url !== 'undefined' && sl_ajax_object.ajax_url != '' && slyrmc_info !== 0) {

        slyrmc_load_fast_menu();

        if (preloaded_info == 0) {

            slyrmc_check_slyr_page_params();

        } else {

            if (preloaded_url != '') {

                window.history.pushState({}, null, slyrmc_html_entities(preloaded_url));

            }

        }

        Shadowbox.init();

        $("#sl_search").submit(function (event) {

            var search_value = $("#sl_search_value").val();

            if (search_value != '') {

                $.ajax({
                    type: 'post',
                    url: sl_ajax_object.ajax_url,
                    data: {
                        action:'slyrmc_catalog_control',
                        endpoint: 'search_item',
                           search_value: search_value,
                           web_url: slyrmc_page_home_url,
                           _ajax_nonce: sl_ajax_object.nonce
                    }
                })
                    .done(function (result) {

                        if (result == 0) {

                            $("#sl_search_value").val('');
                            slyrmc_load_catalog(0);

                        } else {

                            if (result.type == 'c') {

                                slyrmc_load_catalog(result.id);

                            } else if (result.type == 'p') {

                                slyrmc_load_product(result.id);

                            }

                        }

                    });

            }

            event.preventDefault();

        });

    }

}

document.addEventListener('DOMContentLoaded', function () {

    slyrmc_load_vars();

});

