// back button
    if ((!isset['user_id']===admin)){
        window.location.href = '../administrator_dashboard.php';
    }else if ((!isset['userd_id']===patient)){
        window.location.href = '../patient_dashboard.php';
    }else {
        window.location.href = '../pharmacists_dashboard.php';
    }
