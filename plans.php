<?php
// gdm_api/api/diet/plans.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse("error", "Method not allowed", null, 405);
}

// Mock Data for Indian Diet Plans (Since we don't have a table for static plans in the schema requirement, serving static JSON)
$diet_plans = [
    "South Indian" => [
        "Breakfast" => "Idli (2) + Sambar (1 cup) OR Dosa (1) + Chutney",
        "Mid-Morning" => "Buttermilk (1 glass) OR Fruit (Guava/Apple)",
        "Lunch" => "Brown Rice (1 cup) + Vegetable Sambar + Greens (Keerai) + Fish Curry (Optional)",
        "Evening" => "Sundal (Chickpeas) 1 cup + Tea (No sugar)",
        "Dinner" => "Chapati (2) + Vegetable Kurma OR Broken Wheat Upma"
    ],
    "North Indian" => [
        "Breakfast" => "Vegetable Poha (1 cup) OR Stuffed Methi Paratha (1) + Curd",
        "Mid-Morning" => "Roasted Chana + Lemon Water",
        "Lunch" => "Roti (2) + Dal Tadka + Seasonal Sabzi + Salad",
        "Evening" => "Sprouts Chaat + Green Tea",
        "Dinner" => "Roti (1-2) + Paneer Bhurji / Chicken Curry"
    ],
    "Tamil Traditional" => [
        "Breakfast" => "Ragi Koozh OR Kanjeevaram Idli",
        "Lunch" => "Red Rice + Rasam + Poriyal (Beans/Carrot)",
        "Dinner" => "Millet Dosa + Tomato Chutney"
    ]
];

sendResponse("success", "Diet plans fetched", $diet_plans, 200);
?>
