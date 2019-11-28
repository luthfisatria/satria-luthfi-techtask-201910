<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LaunchController extends AbstractFOSRestController
{
    /**
     * @Route("/launch", name="launch")
     */

    public function getLaunchAction(Request $recipe)
    {
        $ingredients = self::getLaunchIngredientsAction(false);
        $recipes = self::getLaunchRecipesAction(false);

        $oldest = min(array_column($ingredients, 'use-by'));

        $listIngredients = array();
        array_map(function($items) use(&$listIngredients, $oldest){
            $items = (array)$items;
            if($items['use-by'] > $oldest){
                $keys = $items['title'];
                $listIngredients[$items['title']] = [
                    'best_before' => $items['best-before'],
                    'use_by' => $items['use-by']
                ];
            }
        }, $ingredients);

        uasort($listIngredients, function($a, $b){
            $key = 'best_before';
                if($a[$key] == $b[$key]){
                    return 0;
                }

                return ($a[$key] < $b[$key]) ? 1 : -1;
        });

        $keysIngredients = array_keys($listIngredients);
        $availableRecipe = array_filter($recipes, function($item) use($keysIngredients){
            return count(array_diff($item->ingredients, $keysIngredients)) == 0 ? 1 : 0;
        });

        $oldest = end($keysIngredients);

        foreach ($availableRecipe as $key => $value) {
            if(in_array($oldest, $value->ingredients) == true){
                unset($availableRecipe[$key]);
                $availableRecipe[] = $value;
            }
        }

        return new JsonResponse(
            array_values($availableRecipe),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    public function getLaunchPingAction(){
        return new JsonResponse(
            'Ping',
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    public function getLaunchIngredientsAction(){
        $args = func_get_args();
        $file_path = __DIR__."/../Ingredient/data.json";
        $ingredients = "Not Available";
        if(file_exists($file_path)){
            $ingredients = json_decode(file_get_contents($file_path))->ingredients;
        }

        if(empty($args)){
            return new JsonResponse(
               $ingredients,
                Response::HTTP_OK,
                ['content-type' => 'application/json']
            );
        }
        return $ingredients;
    }

    public function getLaunchFindAction(Request $request){
        $title = $request->query->get('ingredient');
        $ingredients = self::getLaunchIngredientsAction(false);
        if(!empty($title)){
            $ingredients = array_filter($ingredients, function($item) use($title){
                return strtolower($item->title) == strtolower($title) ? 1 : 0;
            });
            $ingredients = !empty($ingredients) ? array_values($ingredients) : 'Not Found';
        }
        return new JsonResponse(
            $ingredients,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    public function getLaunchRecipesAction(){
        $args = func_get_args();
        $file_path = __DIR__."/../Recipe/data.json";
        $recipe = "Not Available";
        if(file_exists($file_path)){
            $recipe = json_decode(file_get_contents($file_path))->recipes;
        }

        if(empty($args)){
            return new JsonResponse(
               $recipe,
                Response::HTTP_OK,
                ['content-type' => 'application/json']
            );
        }
        return $recipe;
    }

}
