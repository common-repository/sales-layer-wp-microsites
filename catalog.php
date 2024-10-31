<div id="slyr_catalogue">
    <div id="search_bar">
        <form id="sl_search" class="form-inline">
            <i class="fas fa-search" aria-hidden="true"></i>
            <label><input id="sl_search_value" class="form-control form-control-sm ml-3 w-75" type="text"
                          placeholder="Search item" aria-label="Search"></label>
        </form>
    </div>
    <div id="site-wrapper" class="show-nav">
        <div id="site-canvas">
            <div id="box_menu">
                <ul class="accordion lev_top" id="menu_pr">
                    <li class="not-accordion">
                        <a class="url_ldg" href="#" onclick="return false;" title=""
                           data-original-title="Panel principal">
                            <i class="icon fui-list-large-thumbnails"></i>
                            <em>Start</em>
                        </a>
                    </li>
                    <li class="accordion-group">
                        <ul class="accordion-toggle" data-toggle="collapse" data-parent="#menu_pr"
                            id="collapse_catalog_1"></ul>
                    </li>
                </ul>
            </div>
            <div id="box_info">
                <nav class="navbar" role="navigation">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse"
                                data-target="#navbar-collapse-01">
                            <span class="sr-only">Toggle navigation</span>
                        </button>
                        <a class="navbar-brand" href="#" title="Menu" onclick="return false;">
                            <span class="fa fa-bars toggle-nav"></span>
                        </a>
                    </div>
                    <div class="collapse navbar-collapse" id="navbar-collapse-01">
                        <ul class="nav navbar-nav">
                            <li>
                                <ul class="breadcrumb" id="breadcrumb_ul"><?php

                                    if (is_array($print_data) && isset($print_data['breadcrumb']) && !empty($print_data['breadcrumb'])) {
                                        echo $print_data['breadcrumb'];
                                    }

                                    ?></ul>
                            </li>
                        </ul>
                        <div id="gnrl_ldng" class="slyr_cat_prld"></div>
                    </div>
                </nav>
                <div class="box_list md_vw tp_imgs" id="div_category"
                     style="overflow: hidden;<?php if (is_array($print_data) && isset($print_data['product'])) {
                                        echo 'display: none;';
                                    } ?>">
                    <?php

                    if (is_array($print_data) && isset($print_data['no_elements']) && !empty($print_data['no_elements'])) {
                        echo $print_data['no_elements'];
                    } else {
                        if (is_array($print_data) && isset($print_data['categories']) && !empty($print_data['categories'])) {
                            echo $print_data['categories'];
                        }

                        if (is_array($print_data) && isset($print_data['products']) && !empty($print_data['products'])) {
                            echo $print_data['products'];
                        }
                    }
                    ?></div>
                <div id="site-canvas-product"
                    <?php if (is_array($print_data) && isset($print_data['product'])) {
                        echo 'style="overflow: hidden; display: block;"';
                    } ?>
                >
                    <div class="detail-wrapper row col-md-12">
                        <div id="detail-box" class="row">
                            <div id="gallery" class="gallery"><?php

                                if (is_array($print_data) && isset($print_data['product']['gallery']) && $print_data['product']['gallery']) {
                                    echo $print_data['product']['gallery'];
                                }

                                ?></div>
                            <div id="product_info">
                                <h2 id="h_product_name"><?php

                                    if (is_array($print_data) && isset($print_data['product']['product_name']) && $print_data['product']['product_name']) {
                                        echo $print_data['product']['product_name'];
                                    }

                                    ?></h2>
                                <p id="p_product_description"><?php

                                    if (is_array($print_data) && isset($print_data['product']['product_description']) && $print_data['product']['product_description']) {
                                        echo $print_data['product']['product_description'];
                                    }

                                    ?></p>
                                <div id="p_characteristics"><?php

                                    if (is_array($print_data) && isset($print_data['product']['p_characteristics']) && $print_data['product']['p_characteristics']) {
                                        echo $print_data['product']['p_characteristics'];
                                    }

                                    ?></div>
                            </div>
                            <div id="p_formats"><?php

                                if (is_array($print_data) && isset($print_data['product']['p_formats']) && $print_data['product']['p_formats']) {
                                    echo $print_data['product']['p_formats'];
                                }

                                ?></div>
                            <?php if (is_array($print_data) && isset($print_data['product'])) {
                                    if (isset($print_data['product']['catalogue_id'])) {
                                        $product_catalogue_id = $print_data["product"]["catalogue_id"];
                                    } else {
                                        $product_catalogue_id = '';
                                    } ?>
                                <div><a href="#" id="backcatalog" class="btn btn-lg btn-primary"
                                        onclick="slyrmc_back_catalog('<?php echo esc_js($product_catalogue_id); ?>'); return false;">&larr;
                                        back</a></div>
                            <?php
                                } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>