<?php

namespace App\Services\ThemeSetting;

use App\Repositories\ThemeSettingRepository;
use Exception;

class ThemeSettingService
{
    public function __construct(
        protected ThemeSettingRepository $themeSettingRepository
    ){}

    public function getAllThemes($select= ['*'])
    {
        return $this->themeSettingRepository->getAll($select);
    }


    /**
     * @throws Exception
     */
    public function findThemeById($id, $select=['*'])
    {

        return $this->themeSettingRepository->find($id,$select);

    }

    /**
     * @throws Exception
     */
    public function saveTheme($validatedData)
    {
        return $this->themeSettingRepository->store($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateTheme($id, $validatedData)
    {

        $themeDetail = $this->findThemeById($id);
        return $this->themeSettingRepository->update($themeDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteTheme($id): bool
    {

        $themeDetail = $this->findThemeById($id);

        return $this->themeSettingRepository->delete($themeDetail);


    }


}
