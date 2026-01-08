<?php
include_once 'config/db.php';

try {
    // 1. Ensure user_profiles table matches requirements
    // (Already exists in database.sql, but let's make sure)
    
    // 2. Add sample education data
    $check_edu = $conn->query("SELECT COUNT(*) FROM education_content")->fetchColumn();
    if ($check_edu == 0) {
        $qas = [
            ["What is Gestational Diabetes?", "Gestational diabetes is high blood sugar that develops during pregnancy and usually disappears after giving birth."],
            ["How is it managed?", "It is managed through healthy eating, regular exercise, and sometimes medication like insulin."],
            ["Will it affect my baby?", "If well-managed, most women have healthy babies. If not, it can lead to high birth weight and other complications."],
            ["Do I need a special diet?", "Focus on whole grains, lean proteins, and vegetables. Limit sugary foods and drinks."],
            ["How often should I check my sugar?", "Typically 4 times a day: after waking up and 1-2 hours after each main meal."]
        ];
        
        $stmt = $conn->prepare("INSERT INTO education_content (title, content_body, category) VALUES (?, ?, 'General')");
        foreach ($qas as $qa) {
            $stmt->execute($qa);
        }
    }

    echo "<h1>Profile & Education Setup Complete</h1>";
} catch(PDOException $e) {
    echo "<h1>Error</h1>" . $e->getMessage();
}
?>
