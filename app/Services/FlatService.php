<?php

namespace App\Services;

use App\Dto\BannerDto;
use App\Models\FavoriteFlat;
use App\Services\FeedsSettingsService;
use App\Dto\FlatDto;
use App\Dto\FlatFilterDto;
use App\Dto\FlatMetaDto;
use App\Dto\DeveloperDto;
use App\Dto\DeveloperDataDto;
use App\Dto\MetroDto;
use App\Dto\ImageDto;
use App\Dto\DeveloperMetaDto;
use Illuminate\Support\Facades\DB;

class FlatService
{
    private const FLAT_MAP = [
        'developerName' => 'застройщик',
        self::FLAT_ROOMS => 'комнат',
        self::FLAT_PRICE => 'цена',
        self::FLAT_PRICE_TOMORROW => 'цена завтра',
        'residentialComplex' => 'ЖК',
        self::FLAT_MORTGAGE_PAYMENT => 'ипотечный платеж',
        self::FLAT_FLOOR => 'этаж',
        'floorCount' => 'этажей всего',
        self::FLAT_AREA => 'площадь',
        'housing' => 'корпус',
        'housingTotal' => 'всего корпусов',
        'section' => 'секция',
        'developerPhone' => 'телефон застройщика',
        'deadline' => 'год сдачи ключей',
        'deadlineQuarter' => 'квартал сдачи ключей',
        self::OFFER_DAY => 'предложение дня',
        self::FLAT_GOOD_PRICE => 'хорошая цена',
        self::FLAT_IMAGES => 'фото (через ;)',
        self::FLAT_SALE_PERCENT => 'скидка%',
        self::FLAT_COUNT_FAVORITES => 'в избраном старт'
    ];

    private const FLAT_TAG_PREFIX = 'тег:';
    private const FLAT_MORTGAGE_PAYMENT = 'mortgagePayment';
    private const FLAT_COUNT_FAVORITES = 'countFavorites';
    private const FLAT_ID = 'id';
    private const FLAT_DEVELOPER_NAME = 'developerName';
    private const FLAT_FLOOR = 'floor';
    private const FLAT_ROOMS = 'rooms';
    private const FLAT_AREA = 'area';
    private const OFFER_DAY = 'offerDay';
    private const FLAT_GOOD_PRICE = 'goodPrice';
    private const FLAT_PRICE = 'price';
    private const FLAT_SALE_PERCENT = 'salePercent';
    private const FLAT_PRICE_TOMORROW = 'priceTomorrow';
    private const METRO_NAME = 'метро';
    private const METRO_COLOR = 'цвет ветки (HEX)';
    private const METRO_ON_FOOT_TIME = 'мин пешком до метро';
    private const METRO_ON_AUTO_TIME = 'мин на транспорте до метро';
    private const FLAT_IMAGES = 'images';

    private const DEVELOPER_MAP = [
        'developerName' => 'застройщик',
        self::DEVELOPER_LOGO => 'лого',
        self::DEVELOPER_BACKGROUND => 'обложка',
        self::DEVELOPER_TIMER => 'дата окончания таймера'
    ];

    private const DEVELOPER_TIMER = 'timerDateTime';
    private const DEVELOPER_LOGO = 'logo';
    private const DEVELOPER_BACKGROUND = 'background';


    private const DEVELOPER_BANNER_IMAGE = 'image';
    private const DEVELOPER_BANNER_COLOR = 'color';
    private const DEVELOPER_BANNER_HEADER = 'header';
    private const DEVELOPER_BANNER_DESCRIPTION = 'description';
    private const DEVELOPER_BANNER_COLOR_TEXT = 'colorText';

    private const DEVELOPER_BANNER_MAP = [
        self::DEVELOPER_BANNER_IMAGE => 'баннер картинка',
        self::DEVELOPER_BANNER_HEADER => 'баннер заголовок',
        self::DEVELOPER_BANNER_DESCRIPTION => 'баннер текст',
        self::DEVELOPER_BANNER_COLOR => 'баннер цвет',
        self::DEVELOPER_BANNER_COLOR_TEXT => 'баннер цвет текста',
    ];

    public static function getDevelopersMeta(?FlatFilterDto $flatFilterDto = null): array
    {
        $developersMeta = [
            'tags' => []
        ];

        foreach (self::getDevelopers($flatFilterDto) as $developer) {
            $developersMeta['tags'] = array_values(array_unique(array_merge($developer->tags, $developersMeta['tags'])));
        }

        return $developersMeta;
    }

    /**
     * @param FlatDto[]|null $flats
     */
    public static function getFlatsMeta(?array $flats = null): FlatMetaDto
    {
        $flatMetaDto = new FlatMetaDto;

        $developerFlatCountByNames = [];
        $floors = [];
        $rooms = [];
        $deadlines = [];
        $tags = [];
        $priceFrom = null;
        $priceTo = null;
        $mortgagePaymentFrom = null;
        $mortgagePaymentTo = null;

        foreach ($flats ?? self::getFlats() as $flat) {
            if (null !== $flat->floor) {
                $floors[] = $flat->floor;
            }
            if (null !== $flat->rooms) {
                $rooms[] = $flat->rooms;
            }
            if (null !== $flat->deadline) {
                $deadlines[] = $flat->deadline;
            }
            if (null !== $flat->developer && null !== $flat->developer->developerName) {
                if (isset($developerFlatCountByNames[$flat->developer->developerName])) {
                    $developerFlatCountByNames[$flat->developer->developerName]++;
                } else {
                    $developerFlatCountByNames[$flat->developer->developerName] = 1;
                }
            }
            if (null !== $flat->price) {
                if (null === $priceFrom || $priceFrom > $flat->price) {
                    $priceFrom = $flat->price;
                }
                if (null === $priceTo || $priceTo < $flat->price) {
                    $priceTo = $flat->price;
                }
            }
            if (null !== $flat->mortgagePayment) {
                if (null === $mortgagePaymentFrom || $mortgagePaymentFrom > $flat->mortgagePayment) {
                    $mortgagePaymentFrom = $flat->mortgagePayment;
                }
                if (null === $mortgagePaymentTo || $mortgagePaymentTo < $flat->mortgagePayment) {
                    $mortgagePaymentTo = $flat->mortgagePayment;
                }
            }
            $tags = array_merge($tags, $flat->tags);
        }

        if (in_array('📉 Хорошая цена', $tags)) {
            $tags = array_values(array_unique(array_merge(['📉 Хорошая цена'], $tags)));
        }

        $developersMetaList = [];
        foreach ($developerFlatCountByNames as $developerName => $flatCount) {
            $developerMetaDto = new DeveloperMetaDto;
            $developerMetaDto->developerName = $developerName;
            $developerMetaDto->flatCount = $flatCount;
            $developersMetaList[] = $developerMetaDto;
        }
        sort($floors);
        sort($rooms);
        sort($deadlines);
        $flatMetaDto->developers = $developersMetaList;
        $flatMetaDto->priceFrom = $priceFrom;
        $flatMetaDto->priceTo = $priceTo;
        $flatMetaDto->mortgagePaymentFrom = $mortgagePaymentFrom;
        $flatMetaDto->mortgagePaymentTo = $mortgagePaymentTo;
        $flatMetaDto->tags = array_values(array_unique($tags));
        $flatMetaDto->deadlines = array_values(array_unique($deadlines));
        $flatMetaDto->rooms = array_values(array_unique($rooms));
        $flatMetaDto->floors = array_values(array_unique($floors));

        return $flatMetaDto;
    }
    /**
     * @return FlatDto[]
     */
    public static function getFlats(?FlatFilterDto $flatFilterDto = null, $excludeDeleted = true): array
    {
        $flats = [];
        $developerDataDtoByNameList = self::getDevelopersDataByNameList();
        $favoriteCountByFlatIds = [];
        foreach ( FavoriteFlat::groupBy('flat_id')->select('flat_id', DB::raw('count(*) as count'))->get() as $item) {
            $favoriteCountByFlatIds[$item['flat_id']] = $item['count'];
        }
        foreach (GoogleDataService::getFlats() as $flatData) {
            $flat = self::getFlatDtoByRow($flatData, $developerDataDtoByNameList, $favoriteCountByFlatIds);
            if (null === $flat->developer) {
                continue;
            }
            $flats[] = $flat;
        }

        $filterFlats = [];

        foreach ($flats as $flat) {
            if (true === $excludeDeleted && true === $flat->isDeleted) {
                continue;
            }
            if (null !== $flatFilterDto) {
                if (!empty($flatFilterDto->floors) && !in_array($flat->floor, $flatFilterDto->floors)) {
                    continue;
                }
                if (!empty($flatFilterDto->rooms) && !in_array($flat->rooms, $flatFilterDto->rooms)) {
                    continue;
                }
                if (null !== $flatFilterDto->priceFrom) {
                    if (null === $flat->price || $flat->price < $flatFilterDto->priceFrom) {
                        continue;
                    }
                }
                if (null !== $flatFilterDto->priceTo) {
                    if (null === $flat->price || $flat->price > $flatFilterDto->priceTo) {
                        continue;
                    }
                }
                if (null !== $flatFilterDto->mortgagePaymentFrom) {
                    if (null === $flat->mortgagePayment || $flat->mortgagePayment < $flatFilterDto->mortgagePaymentFrom) {
                        continue;
                    }
                }
                if (null !== $flatFilterDto->mortgagePaymentTo) {
                    if (null === $flat->mortgagePayment || $flat->mortgagePayment > $flatFilterDto->mortgagePaymentTo) {
                        continue;
                    }
                }
                if (!empty($flatFilterDto->deadlines) && !in_array($flat->deadline, $flatFilterDto->deadlines)) {
                    continue;
                }
                if (!empty($flatFilterDto->developerNames) && !in_array($flat->developer->developerName, $flatFilterDto->developerNames)) {
                    continue;
                }
                foreach ($flatFilterDto->tags as $searchTag) {
                    if (!in_array($searchTag, $flat->tags)) {
                        continue 2;
                    }
                }
                if (null !== $flatFilterDto->offerDay && $flatFilterDto->offerDay !== $flat->offerDay) {
                    continue;
                }if (null !== $flatFilterDto->goodPrice && $flatFilterDto->goodPrice !== $flat->goodPrice) {
                    continue;
                }
            }
            $filterFlats[] = $flat;
        }

        return $filterFlats;
    }

    /**
     * @return DeveloperDataDto[]
     */
    private static function getDevelopersDataByNameList(): array
    {
        $developerDataDtoByNameList = [];
        foreach (GoogleDataService::getDevelopers() as $i => $developerData) {
            $developerDataDto = self::getDeveloperDtoByRow($developerData, $i);
            $developerDataDtoByNameList[$developerDataDto->developerName] = $developerDataDto;
        }

        return $developerDataDtoByNameList;
    }

    /**
     * @return DeveloperDto[]
     */
    public static function getDevelopers(?FlatFilterDto $flatFilterDto = null): array
    {
        $developers = [];
        $developerDataDtoByNameList = self::getDevelopersDataByNameList();

        foreach (self::getFlats($flatFilterDto) as $flat) {
            if (null === $flat->developer) {
                continue;
            }
            if (!isset($developers[$flat->developer->developerName])) {
                if (!isset($developerDataDtoByNameList[$flat->developer->developerName])) {
                    continue;
                }
                $developerDataDto = $developerDataDtoByNameList[$flat->developer->developerName] ?? null;
                $developerDto = new DeveloperDto();
                $developerDto->tags = $flat->tags;
                $developerDto->flatCount = 1;
                $developerDto->developerName = $flat->developer->developerName;
                $developerDto->priceFrom = $flat->price;
                $developerDto->logo = $developerDataDto->logo ?? null;
                $developerDto->banner = $developerDataDto->banner ?? null;
                $developerDto->background = $developerDataDto->background ?? null;
                $developerDto->timerDateTime = $developerDataDto->timerDateTime ?? null;
                $developerDto->maxSalePercent = $flat->salePercent;
                $developers[$flat->developer->developerName] = $developerDto;
            } else {
                $developers[$flat->developer->developerName]->flatCount ++;
                $developers[$flat->developer->developerName]->tags = array_values(array_unique(array_merge($developers[$flat->developer->developerName]->tags, $flat->tags)));
                if (in_array('📉 Хорошая цена', $developers[$flat->developer->developerName]->tags)) {
                    $developers[$flat->developer->developerName]->tags = array_values(array_unique(array_merge(['📉 Хорошая цена'], $developers[$flat->developer->developerName]->tags)));
                }


                if ($flat->price < $developers[$flat->developer->developerName]->priceFrom) {
                    $developers[$flat->developer->developerName]->priceFrom = $flat->price;
                }

                if (
                    null !== $flat->salePercent &&
                    (
                        null === $developers[$flat->developer->developerName]->maxSalePercent ||
                        $developers[$flat->developer->developerName]->maxSalePercent < $flat->salePercent
                    )
                ) {
                    $developers[$flat->developer->developerName]->maxSalePercent = $flat->salePercent;
                }
            }
        }

        $sortDevelopers = [];

        foreach ($developers as $developer) {
            $sortDevelopers[$developerDataDtoByNameList[$developer->developerName]->sort] = $developer;
        }
        ksort($sortDevelopers);

        return array_values($sortDevelopers);
    }

    private static function getDeveloperDtoByRow(array $developerData, int $i): DeveloperDataDto
    {
        $developerDataDto = new DeveloperDataDto();
        $developerDataDto->sort = $i;
        foreach (self::DEVELOPER_MAP as $keyDto => $keyRow) {
            if (isset($developerData[$keyRow])) {
                switch ($keyDto) {
                    case self::DEVELOPER_TIMER:
                        $developerDataDto->{$keyDto} = new \DateTime($developerData[$keyRow]);
                        break;
                    case self::DEVELOPER_LOGO:
                    case self::DEVELOPER_BACKGROUND:
                        if (!empty($developerData[$keyRow])) {
                            $imageDto = new ImageDto();
                            $imageDto->link = $developerData[$keyRow];
                            $developerDataDto->{$keyDto} = $imageDto;
                        }

                        break;
                    default:
                        $developerDataDto->{$keyDto} = $developerData[$keyRow];
                        break;
                }

            }
        }
        $bannerDto = new BannerDto();
        foreach (self::DEVELOPER_BANNER_MAP as $keyDto => $keyRow) {
            if (isset($developerData[$keyRow])) {
                switch ($keyDto) {
                    case self::DEVELOPER_BANNER_COLOR:
                    case self::DEVELOPER_BANNER_COLOR_TEXT:
                    case self::DEVELOPER_BANNER_HEADER:
                    case self::DEVELOPER_BANNER_DESCRIPTION:
                        $bannerDto->{$keyDto} = $developerData[$keyRow];
                        break;
                    case self::DEVELOPER_BANNER_IMAGE:
                        $imageDto = new ImageDto();
                        $imageDto->link = $developerData[$keyRow];
                        $bannerDto->image = $imageDto;
                        break;
                }
            }
        }
        $developerDataDto->banner = $bannerDto;
        return $developerDataDto;
    }

    /**
     * @param array $flatData
     * @param DeveloperDataDto[] $developerDataDtoByNameList
     */
    private static function getFlatDtoByRow(array $flatData, array $developerDataDtoByNameList, array $favoriteCountByFlatIds): FlatDto
    {
        $flatDto = new FlatDto();
        $flatTags = [];
        $flatDto->flatId = self::getFlatIdByFlatData($flatData);
        foreach (self::FLAT_MAP as $keyDto => $keyRow) {
            if (isset($flatData[$keyRow])) {
                switch ($keyDto) {
                    case self::OFFER_DAY:
                    case self::FLAT_GOOD_PRICE:
                        $flatDto->{$keyDto} = !empty($flatData[$keyRow]);
                        break;
                    case self::FLAT_PRICE:
                    case self::FLAT_PRICE_TOMORROW:
                    case self::FLAT_SALE_PERCENT:
                        $flatDto->{$keyDto} = (int) str_replace(' ', '', $flatData[$keyRow]);
                        break;
                    case self::FLAT_COUNT_FAVORITES:
                        $flatDto->{$keyDto} = ((int) $flatData[$keyRow]) + ($favoriteCountByFlatIds[$flatDto->flatId] ?? 0);
                        break;
                    case self::FLAT_ROOMS:
                        $value = null;
                        if (isset($flatData[$keyRow])) {
                            $value = $flatData[$keyRow];
                            if ('студия' === strtolower($value)) {
                                $value = 0;
                            }
                        }
                        $flatDto->{$keyDto} = (int) $value;
                        break;
                    case self::FLAT_IMAGES:
                        $flatDto->{self::FLAT_IMAGES} = [];
                        foreach (explode(';', $flatData[self::FLAT_MAP[self::FLAT_IMAGES]] ?? '') as $imageLink) {
                            if (empty($imageLink)) {
                                continue;
                            }
                            $imageDto = new ImageDto();
                            $imageDto->link = $imageLink;
                            $flatDto->{self::FLAT_IMAGES}[] = $imageDto;
                        }

                        break;
                    case self::FLAT_MORTGAGE_PAYMENT:
                        $flatDto->{$keyDto} = !empty($flatData[$keyRow]) ? (int) $flatData[$keyRow] : null;

                        break;
                    default:
                        $flatDto->{$keyDto} = !empty($flatData[$keyRow]) ? $flatData[$keyRow] : null;
                        break;
                }
            }
        }

        foreach ($flatData as $key => $value) {
            if (str_starts_with($key, self::FLAT_TAG_PREFIX) && $value === 'да') {
                $flatTags[] = substr($key, strlen(self::FLAT_TAG_PREFIX));
            }
        }
        $flatDto->tags = array_unique($flatTags);
        $flatDto->code = empty($flatData[self::FLAT_ID]) ? null : $flatData[self::FLAT_ID];

        $flatDto->developer = $developerDataDtoByNameList[$flatData[self::FLAT_MAP[self::FLAT_DEVELOPER_NAME]] ?? null] ?? null;

        $metroDto = self::getMetroDtoByRow($flatData);
        if (null !== $metroDto) {
            $flatDto->metro = $metroDto;
        }

        $flatDto->isDeleted = $flatData['deleted'] ?? false;
        return $flatDto;
    }

    public static function getFlatIdByFlatData($flatData): string
    {
        if (!empty($flatData[self::FLAT_ID])) {
            return hash('ripemd160', $flatData[self::FLAT_MAP[self::FLAT_DEVELOPER_NAME]] . $flatData[self::FLAT_ID]);
        } else {
            return hash('ripemd160', $flatData[self::FLAT_MAP[self::FLAT_DEVELOPER_NAME]] . $flatData[self::FLAT_MAP[self::FLAT_PRICE]] . $flatData[self::FLAT_MAP[self::FLAT_AREA]] . $flatData[self::FLAT_MAP[self::FLAT_FLOOR]]);
        }
    }

    private static function getMetroDtoByRow(array $flatData): ?MetroDto
    {
        $metroDto = new MetroDto();
        $name = $flatData[self::METRO_NAME] ?? null;
        $color = $flatData[self::METRO_COLOR] ?? null;
        $onFootTime = $flatData[self::METRO_ON_FOOT_TIME] ?? null;
        $onAutoTime = $flatData[self::METRO_ON_AUTO_TIME] ?? null;

        if (null === $name || null === $color || null === $onFootTime) {
            return null;
        }
        $metroDto->name = $name;
        $metroDto->color = $color;
        $metroDto->onFootTime = $onFootTime;
        $metroDto->onAutoTime = $onAutoTime;

        return $metroDto;
    }
}
