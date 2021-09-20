<section class="categories-products">
    <h2 class="categories-products__title text-sm-center">{l s='Products categories' d='Modules.Featuredproducts.Shop'}</h2>
    {foreach from=$categories item="category"}
        <h3 class="categories-products__cat-name">{$category['categoryName']}</h3>
        <div class="categories-products__products owl-carousel owl-theme">
            {foreach from=$category['products'] item="product"}
                {include file="module:productcategories/views/templates/hook/categories_product_miniature.tpl" product=$product}
            {/foreach}
        </div>
        <div class="text-sm-center">
            <a class="categories-products__cat-link btn btn-primary" href="{$category['categoryLink']}">
                {l s='See more products' d='Modules.Productcategories.Shop'}
            </a>
        </div>
    {/foreach}
</section>