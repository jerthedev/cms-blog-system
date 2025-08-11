<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Facades;

use Illuminate\Support\Facades\Facade;
use JTD\CMSBlogSystem\Configuration\BlogConfig as BlogConfigManager;

/**
 * Blog Configuration Facade
 *
 * Provides easy access to blog configuration settings.
 *
 * @method static string getRoutePrefix()
 * @method static int getPostsPerPage()
 * @method static int getRelatedPostsCount()
 * @method static int getTagCloudLimit()
 * @method static bool isCacheEnabled()
 * @method static int getCacheTtl()
 * @method static string getThemeName()
 * @method static string getThemeLayout()
 * @method static string getMetaTitleSuffix()
 * @method static bool shouldGenerateSitemap()
 * @method static string getSitemapFrequency()
 * @method static string getMediaDisk()
 * @method static array getFeaturedImageSizes()
 * @method static string getSearchDriver()
 * @method static int getMinQueryLength()
 * @method static int getMaxSearchResults()
 * @method static bool areFeedsEnabled()
 * @method static string getFeedTitle()
 * @method static string getFeedDescription()
 * @method static int getFeedItemsCount()
 * @method static bool isFeatureEnabled(string $feature)
 * @method static array getWebMiddleware()
 * @method static array getApiMiddleware()
 * @method static array toArray()
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogConfig extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return BlogConfigManager::class;
    }
}
