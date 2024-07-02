<?php declare(strict_types=1);

namespace App\Helpers;

use App\Services\ItNetwork\HtmlBuilder;

/**
 * Soubor formátovacích metod pro navigační menu
 */
class MenuHelper
{
	/**
	 * Vyrenderuje menu ze stromu jako vnořené seznamy
	 * @param array $categories Strom kategorií
	 * @param string $parentUrl URL rodičovské kategorie (pro rekurzi)
	 * @return string Výsledné HTML
	 */
	public static function renderCategories($categories, $parentUrl = '')
	{
		$builder = new HtmlBuilder();

        $builder->startElement('ul', array('class' => 'filter-catagories menu', 'id' => 'menu'));

        foreach ($categories as $index => $category)
        {
            $urlCategory = $parentUrl . '/' . $category['url'];

            $builder->startElement('li');
            $builder->addValueElement('a', $category['title'], array(
                'class' => 'ui-state-disabled menu-link',
                'href' => '/produkty/index' . $urlCategory,
                'data-path' => $urlCategory));

            if($category['subcategories']){
                $builder->startElement('ul', array('class' => 'filter-catagories nested submenu'));
                foreach ($category['subcategories'] as $index_subcategory => $subcategory){
                    $urlSubCategory = $parentUrl . '/' . $subcategory['url'];

                    $builder->startElement('li');
                    $builder->addValueElement('a', $subcategory['title'], array(
                        'href' => '/produkty/index' . $urlCategory . $urlSubCategory,
                        'data-path' => $urlCategory . $urlSubCategory,
                        'class' => 'menu-link'
                    ));
                    $builder->endElement();
                }
                $builder->endElement();
            }
            $builder->endElement();
        }

        $builder->endElement();

		return $builder->render();
	}

    /**
     * Vyrenderuje menu ze stromu jako vnořené seznamy
     * @param array $categories Strom kategorií
     * @param string $parentUrl URL rodičovské kategorie (pro rekurzi)
     * @return string Výsledné HTML
     */
    public static function renderParentCategories($categories, $parentUrl = '')
    {
        $builder = new HtmlBuilder();

        $builder->startElement('ul', array('class' => 'depart-hover'));

        foreach ($categories as $index => $category)
        {
            $url = $parentUrl . '/' . $category['url'];

            $builder->startElement('li');
            $builder->addValueElement('a', $category['title'], array(
                'href' => '/produkty/index' . $url,
                'data-path' => $url,
            ));
            $builder->endElement();
        }

        $builder->endElement();

        return $builder->render();
    }
}
