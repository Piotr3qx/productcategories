{block name='product_miniature_item'}
    <div itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" class="item product">
        {if isset($position)}
        <meta itemprop="position" content="{$position}" />{/if}
        <article class="js-product-miniature" data-id-product="{$product.id_product}"
            data-id-product-attribute="{$product.id_product_attribute}" itemprop="item" itemscope
            itemtype="http://schema.org/Product">
                {block name='product_thumbnail'}
                    <div class="categories-products__image">
                    {if $product.cover}
                        <a href="{$product.url}">
                            <img src="{$product.cover.bySize.home_default.url}"
                                alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
                                data-full-size-image-url="{$product.cover.large.url}" />
                        </a>
                    {else}
                        <a href="{$product.url}">
                            <img src="{$urls.no_picture_image.bySize.home_default.url}" />
                        </a>
                    {/if}
                    </div>
                {/block}

                {block name='product_name'}
                    <h3 class="categories-products__product-title" itemprop="name">
                        <a href="{$product.url}" itemprop="url" content="{$product.url}">{$product.name}</a>
                    </h3>
                {/block}

                {block name='product_price_and_shipping'}
                    {if $product.show_price}
                        <div class="categories-products__prices">
                            {if $product.has_discount}
                                {hook h='displayProductPriceBlock' product=$product type="old_price"}

                                <span class="regular-price"
                                    aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">{$product.regular_price}</span>
                            {/if}

                            {hook h='displayProductPriceBlock' product=$product type="before_price"}

                            <span class="price" aria-label="{l s='Price' d='Shop.Theme.Catalog'}">{$product.price}</span>
                            <div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="invisible">
                                <meta itemprop="priceCurrency" content="{$currency.iso_code}" />
                                <meta itemprop="price" content="{$product.price_amount}" />
                            </div>

                            {hook h='displayProductPriceBlock' product=$product type='unit_price'}

                            {hook h='displayProductPriceBlock' product=$product type='weight'}
                        </div>
                    {/if}
                {/block}

                {include file='catalog/_partials/product-flags.tpl'}

            <form action="{$urls.pages.cart}" method="post" class="add-to-cart-or-refresh categories-products__form">
                <input type="hidden" name="token" value="{$static_token}">
                <input type="hidden" name="id_product" value="{$product.id}" class="product_page_product_id">

                <input 
                    type="number" 
                    name="qty" 
                    value="1"
                    class="input-group" 
                    min="{$product.minimal_quantity}"
                    aria-label="{l s='Quantity' d='Shop.Theme.Actions'}"
                >

                <button
                    class="btn btn-primary categories-products__btn-add-to-cart"
                    data-button-action="add-to-cart" type="submit">
                    <i class="material-icons shopping-cart">&#xE547;</i>
                </button>
            </form>
        </article>
    </div>
{/block}