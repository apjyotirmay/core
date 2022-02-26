<?php
/**
    functions start with push_, pull_, get_, do_ or is_
    push_ is to save to database
    pull_ is to pull from database, returns 1 or 0, saves the output array in $last_data
    get_ is to get usable values from functions
    do_ is for action that doesn't have a database push or pull
    is_ is for a yes/no answer
 * @var object $dash
 */

namespace Wildfire\Core;

class Theme {
    public static $last_error = null; //array of error messages
    public static $last_info = null; //array of info messages
    public static $last_data = null; //array of data to be sent for display
    public static $last_redirect = null; //redirection url
    private object $dash;

    public function __construct() {
        $this->dash = new Dash();
    }

    public function get_menu($slug = '', $css_classes = array('ul' => 'justify-content-center', 'li' => 'nav-item', 'a' => 'nav-link')) {
        $types = $this->dash->getTypes();
        $menus = $this->dash->getMenus();
        $session_user = $this->dash->getSessionUser();

        if (is_array($slug)) {
            $items = $slug;
        } elseif ($slug) {
            $items = $menus[$slug];
        } else {
            $items = 0;
        }

        $op = '';

        if ($items) {
            $op .= '<ul class="' . ($css_classes['ul'] ?? '') . '">';
            if (isset($items['menu'])) {
                foreach ($items['menu'] as $item) {
                    if (
                        isset($item['admin_access_only']) &&
                        $item['admin_access_only'] &&
                        $types['user']['roles'][$session_user[role_slug]]['role'] != 'admin'
                    ) {
                        continue;
                    }

                    if (
                        isset($item['submenu']) &&
                        is_array($item['submenu'])
                    ) {
                        $op .= "<li class='dropdown ${$css_classes['li'] ?? ''}'";
                        $op .= '<li class="dropdown ' . ($css_classes['li'] ?? '') . '"><a class="' . ($css_classes['a'] ?? '') . ' dropdown-toggle" href="#" title="' . ($item['title'] ?? '') . '" role="button" data-toggle="dropdown">' . ($item['name'] ?? '') . '
								</a><div class="dropdown-menu ' . ($css_classes['dropdown'] ?? '') . ' ' . ($item['dropdown_class'] ?? '') . '">';
                        if (isset($item['submenu'])) {
                            foreach ($item['submenu'] as $subitem) {
                                $op .= '<a class="dropdown-item" href="' . ($subitem['href'] ?? '') . '" title="' . ($subitem['title'] ?? '') . '">' . ($subitem['name'] ?? '') . '</a>';
                            }
                        }
                        $op .= '</div></li>';
                    } else if (isset($item['submenu'])) {
                        $submenu = $item['submenu'];
                        $subitems = $this->dash->get_all_ids($item['submenu'], ($types[$submenu]['priority_field'] ?? ''), ($types[$submenu]['priority_order'] ?? ''));
                        $op .= '<li class="dropdown ' . ($css_classes['li'] ?? '') . '"><a class="' . ($css_classes['a'] ?? '') . ' dropdown-toggle" href="#" title="' . ($item['title'] ?? '') . '" role="button" data-toggle="dropdown">' . ($item['name'] ?? '') . '
								</a><div class="dropdown-menu ' . ($css_classes['dropdown'] ?? '') . ' ' . ($item['dropdown_class'] ?? '') . '">';
                        foreach ($subitems as $opt) {
                            $subitem = $this->dash->get_content($opt['id']);
                            $op .= '<a class="dropdown-item" href="/' . $item['submenu'] . '/' . $subitem['slug'] . '">' . ($subitem['title'] ?? '') . '</a>';
                        }
                        $op .= '</div></li>';
                    } else {
                        $data_ext = '';
                        if (isset($item['data'])) {
                            foreach ($item['data'] as $data) {
                                foreach ($data as $k => $v) {
                                    $data_ext .= 'data-' . $k . '="' . $v . '" ';
                                }
                            }
                        }

                        $op .= '<li class="' . ($css_classes['li'] ?? '') . '"><a class="' . ($css_classes['a'] ?? '') . '" ' . ($data_ext) . ' href="' . ($item['href'] ?? '') . '" title="' . ($item['title'] ?? '') . '">' . ($item['name'] ?? '') . '</a></li>';
                    }
                }
            }
            $op .= '
				</ul>';
        } else {
            $op .= '
					<ul class="' . ($css_classes['ul'] ?? '') . '">
					  <li class="' . ($css_classes['li'] ?? '') . '">
					    <a class="' . ($css_classes['a'] ?? '') . ' active" href="#">Active</a>
					  </li>
					  <li class="' . ($css_classes['li'] ?? '') . '">
					    <a class="' . ($css_classes['a'] ?? '') . '" href="#">Link</a>
					  </li>
					  <li class="' . ($css_classes['li'] ?? '') . '">
					    <a class="' . ($css_classes['a'] ?? '') . '" href="#">Link</a>
					  </li>
					  <li class="' . ($css_classes['li'] ?? '') . '">
					    <a class="' . ($css_classes['a'] ?? '') . ' disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
					  </li>
					</ul>';
        }

        return $op;
    }
}
