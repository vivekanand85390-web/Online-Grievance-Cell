<?php
include_once 'includes/functions.php';

// Fetch logged-in user's complaint stats for homepage display
$hp_total = 0; $hp_pending = 0; $hp_inprocess = 0; $hp_closed = 0;
$hp_complaints = [];
if (isLoggedIn()) {
    $hp_uid = $_SESSION['user_id'];
    $s = $pdo->prepare("SELECT COUNT(*) FROM grievances WHERE user_id = ?");
    $s->execute([$hp_uid]); $hp_total = $s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM grievances WHERE user_id = ? AND status = 'Pending'");
    $s->execute([$hp_uid]); $hp_pending = $s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM grievances WHERE user_id = ? AND status = 'In Progress'");
    $s->execute([$hp_uid]); $hp_inprocess = $s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM grievances WHERE user_id = ? AND status IN ('Resolved','Rejected')");
    $s->execute([$hp_uid]); $hp_closed = $s->fetchColumn();

    // Fetch full complaints list for modal
    $s = $pdo->prepare("SELECT g.id, g.subject, g.status, g.created_at, c.name as category
                        FROM grievances g
                        JOIN categories c ON g.category_id = c.id
                        WHERE g.user_id = ?
                        ORDER BY g.created_at DESC");
    $s->execute([$hp_uid]);
    $hp_complaints = $s->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSTI R-Hyderabad - Online Grievance Redressal System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-base: #f7f9fc;
            --nsti-blue: #0f3057;
            --nsti-magenta: #b01c70;
            --text-dark: #2d3748;
            --text-muted: #718096;
            --card-border: rgba(0, 0, 0, 0.06);
            --bg-light: #ffffff;
        }

        body, html {
            margin: 0;
            padding: 0;
            scroll-behavior: smooth;
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-base);
            color: var(--text-dark);
        }

        /* Top Header Branding Section */
                        .header-branding {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1rem;
            width: 100%;
            padding: 1rem 2rem;
        }





        .header-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            justify-self: start;
        }


        .header-logo img {
            width: 110px;
            height: auto;
            object-fit: contain;
        }

        .logo-caption {
            margin: 0;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--nsti-blue);
            text-align: center;
            white-space: nowrap;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-top: 2px solid var(--nsti-magenta);
            padding: 0.4rem 0.5rem 0 0.5rem;
        }

        .logo-caption .caption-location {
            color: var(--nsti-magenta);
            font-weight: 800;
        }

        .header-titles {
            text-align: center;
            justify-self: center;
        }





        .header-titles .main-title {
            font-size: 3.8rem;
            font-weight: 900;
            color: var(--nsti-blue);
            margin: 0;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .header-titles .sub-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--nsti-magenta);
            margin: 0.25rem 0 0 0;
            white-space: nowrap;
        }

        .header-right-logo {
            justify-self: end;
        }


        @media (max-width: 1400px) {
            .header-titles .main-title { font-size: 3.2rem; }
            .header-titles .sub-title { font-size: 1.8rem; }
            .header-branding { padding: 1rem; }
        }

        @media (max-width: 1200px) {
            .header-titles .main-title { font-size: 2.8rem; }
            .header-titles .sub-title { font-size: 1.6rem; }
        }

        @media (max-width: 991px) {
            .header-branding {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
                text-align: center;
                padding: 1rem;
            }
            .header-titles .main-title {
                font-size: 2.8rem;
                white-space: nowrap;
            }
            .header-titles .sub-title {
                font-size: 1.6rem;
                white-space: nowrap;
                margin-top: 0.25rem;
            }
        }
        
        @media (max-width: 576px) {
            .header-titles .main-title {
                font-size: 2.2rem;
                white-space: normal;
                line-height: 1.2;
            }
            .header-titles .sub-title {
                font-size: 1.4rem;
                white-space: normal;
                line-height: 1.3;
            }
        }

        /* Navigation Bar Styles */
        .custom-navbar {
            background-color: var(--nsti-blue);
            box-shadow: 0 4px 15px rgba(15, 48, 87, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            transition: all 0.3s ease;
        }

        .menu-toggle {
            display: none;
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 1.35rem;
            cursor: pointer;
            padding: 0.5rem;
            align-items: center;
            justify-content: center;
        }

        .mobile-brand {
            display: none;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            text-transform: uppercase;
            text-decoration: none;
            letter-spacing: 0.5px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            justify-content: center;
            list-style: none;
            flex-wrap: wrap;
            margin: 0;
            padding: 0;
        }

        .nav-menu li {
            display: flex;
            align-items: center;
        }

        .nav-menu a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.9rem 1.2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            text-transform: capitalize;
        }

        .nav-menu a:hover, .nav-menu a.active {
            color: #ffe600;
            border-bottom-color: var(--nsti-magenta);
        }

        .nav-menu .separator {
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.9rem;
            user-select: none;
        }

        @media (max-width: 768px) {
            .navbar-container {
                justify-content: flex-end;
                flex-wrap: wrap;
                padding: 0.5rem 1rem;
            }

            .menu-toggle {
                display: flex;
            }

            .mobile-brand {
                display: none;
            }

            .nav-menu {
                display: none;
                flex-direction: column;
                width: 100%;
                padding-top: 0.5rem;
            }

            .nav-menu.show {
                display: flex;
            }

            .nav-menu li {
                width: 100%;
                flex-direction: column;
            }

            .nav-menu a {
                padding: 0.6rem 2rem;
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                border-bottom: none;
                border-left: 3px solid transparent;
            }
            .nav-menu a:hover, .nav-menu a.active {
                border-left-color: var(--nsti-magenta);
                border-bottom-color: transparent;
                background-color: rgba(255, 255, 255, 0.05);
            }
            .nav-menu .separator {
                display: none;
            }
        }

        /* Custom Dropdown Styling */
        .nav-menu .dropdown {
            position: relative;
        }

        .nav-menu .dropdown-toggle::after {
            display: none; /* Hide default bootstrap arrow */
        }

        /* Chevron Animation */
        .nav-menu .dropdown-toggle i {
            font-size: 0.75rem;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            margin-left: 0.35rem;
        }

        /* Chevron Rotates when open or hovered */
        .nav-menu .dropdown:hover .dropdown-toggle i,
        .nav-menu .dropdown-toggle[aria-expanded="true"] i {
            transform: rotate(180deg);
        }

        .nav-menu .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            background-color: #0b223e; /* Darker navy for contrast */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            border-radius: 12px;
            padding: 0.6rem 0;
            margin-top: 0;
            min-width: 220px;
            overflow: hidden;
            flex-direction: column;
            z-index: 1000;
        }

        .nav-menu .dropdown-menu li {
            display: block !important;
            width: 100% !important;
        }

        .nav-menu .dropdown-item {
            display: block !important;
            width: 100% !important;
            text-align: left;
            color: #ffffff !important;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.75rem 1.6rem !important;
            border-left: 3px solid transparent !important;
            border-bottom: none !important; /* Remove any border from global menu styles */
            transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
            text-transform: capitalize;
        }

        /* Slide-out indent animation on hover */
        .nav-menu .dropdown-item:hover,
        .nav-menu .dropdown-item:focus,
        .nav-menu .dropdown-item.active {
            color: #ffe600 !important;
            background-color: var(--nsti-magenta) !important;
            border-left-color: #ffe600 !important;
            padding-left: 1.9rem !important; /* Subtle indentation */
        }

        /* Desktop Hover Behavior */
        @media (min-width: 769px) {
            .nav-menu .dropdown:hover .dropdown-menu {
                display: flex !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: translateX(-50%) translateY(0) !important;
            }
            .nav-menu .dropdown-menu {
                display: flex !important;
                opacity: 0;
                visibility: hidden;
                transform: translateX(-50%) translateY(15px);
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }
        }

        /* Mobile Responsive Dropdown Override */
        @media (max-width: 768px) {
            .nav-menu .dropdown {
                width: 100%;
                text-align: center;
            }
            .nav-menu .dropdown-toggle {
                width: 100%;
                justify-content: center;
            }
            .nav-menu .dropdown-menu {
                position: relative !important;
                left: auto !important;
                transform: none !important;
                float: none !important;
                width: 100% !important;
                background-color: rgba(0, 0, 0, 0.15) !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0.5rem 0 !important;
                display: none !important;
            }
            .nav-menu .dropdown-menu.open {
                display: flex !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            .nav-menu .dropdown-item {
                text-align: center;
                color: rgba(255, 255, 255, 0.8) !important;
                padding: 0.6rem 2rem !important;
                background: transparent !important;
                border-left: 3px solid transparent !important;
            }
            .nav-menu .dropdown-item:hover,
            .nav-menu .dropdown-item:focus,
            .nav-menu .dropdown-item.active {
                color: #ffe600 !important;
                border-left-color: var(--nsti-magenta) !important;
                background-color: rgba(255, 255, 255, 0.05) !important;
                padding-left: 2rem !important; /* Keep it consistent on mobile */
            }
        }

        /* Container Padding */
        .page-section {
            padding: 4rem 1.5rem;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Section Headings with Vertical Bar */
        .section-title-wrapper {
            border-left: 5px solid var(--nsti-magenta);
            padding-left: 1rem;
            margin-bottom: 0.25rem;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--nsti-blue);
            margin: 0;
            text-transform: capitalize;
        }

        .section-decor-dots {
            display: flex;
            gap: 5px;
            margin-top: 8px;
            margin-bottom: 2rem;
        }

        .section-decor-dots span {
            width: 6px;
            height: 6px;
            background-color: #3b82f6;
            border-radius: 50%;
        }

        .section-decor-dots span:first-child { background-color: var(--nsti-magenta); }
        .section-decor-dots span:last-child { width: 45px; border-radius: 3px; }

        /* General Card component */
        .section-card {
            background-color: var(--bg-light);
            border-radius: 12px;
            border: 1px solid var(--card-border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        /* About Section */
        .about-text {
            font-size: 1.05rem;
            color: var(--text-dark);
            line-height: 1.7;
            margin: 0;
            text-align: justify;
        }

        /* Vision & Mission Columns */
        .vision-mission-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-top: 1rem;
        }

        .vision-card, .mission-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid var(--card-border);
            padding: 2.2rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            transition: transform 0.3s ease;
        }

        .vision-card:hover, .mission-card:hover {
            transform: translateY(-4px);
        }

        .vm-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .vm-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .vision-icon-bg { background-color: #eff6ff; color: #2563eb; }
        .mission-icon-bg { background-color: #fdf2f8; color: #db2777; }

        .vm-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--nsti-blue);
            margin: 0;
        }

        .vm-desc {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0;
        }

        @media (max-width: 768px) {
            .vision-mission-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1.5rem;
            width: 100%;
        }

        /* Grid spans matching 3 on top, 2 on bottom design */
        .grid-card-3 {
            grid-column: span 2;
        }

        .grid-card-2-first {
            grid-column: 2 / span 2;
        }

        .grid-card-2-second {
            grid-column: 4 / span 2;
        }

        @media (max-width: 991px) {
            .cards-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            .grid-card-3 {
                grid-column: span 2;
            }
            .grid-card-2-first {
                grid-column: span 2;
            }
            .grid-card-2-second {
                grid-column: span 2;
            }
        }

        @media (max-width: 767px) {
            .cards-grid {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
        }
        /* Custom Role Selector Cards matching user's third mockup */
        .role-selector-card {
            border-radius: 28px;
            padding: 3rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 1;
        }

        .role-selector-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.5s ease;
            z-index: -1;
            pointer-events: none;
        }

        .role-selector-card:hover::before {
            opacity: 1;
        }

        .role-selector-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .role-bg-student { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); }
        .role-bg-staff { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .role-bg-parent { background: linear-gradient(135deg, #db2777 0%, #be185d 100%); }
        .role-bg-admin { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }

        .role-avatar-circle {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.95);
            border: 6px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15), inset 0 4px 8px rgba(0,0,0,0.05);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            backdrop-filter: blur(5px);
        }

        .role-selector-card:hover .role-avatar-circle {
            transform: scale(1.08) translateY(-5px);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), inset 0 4px 10px rgba(0,0,0,0.05);
        }

        .role-pill-btn {
            color: #ffffff !important;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.5px;
            padding: 0.8rem 3rem;
            border-radius: 30px;
            text-transform: uppercase;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .role-selector-card:hover .role-pill-btn {
            transform: translateY(-3px) scale(1.02);
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
            color: #ffffff !important;
        }

        .btn-pill-student { /* Managed by global */ }
        .btn-pill-staff { /* Managed by global */ }
        .btn-pill-parent { /* Managed by global */ }
        .btn-pill-admin { /* Managed by global */ }

        .grievance-card {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid var(--card-border);
            padding: 2rem 1.5rem;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .grievance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(15, 48, 87, 0.08);
            border-color: rgba(15, 48, 87, 0.15);
        }

        .grievance-card::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 4px;
            background-color: var(--nsti-magenta);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .grievance-card:hover::after {
            transform: scaleX(1);
        }

        .icon-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.25rem;
            transition: transform 0.3s ease;
        }

        .grievance-card:hover .icon-circle {
            transform: scale(1.06);
        }

        .circle-student { background-color: #e0f2fe; color: #0284c7; }
        .circle-parent { background-color: #fef3c7; color: #d97706; }
        .circle-employee { background-color: #e0f2f1; color: #00796b; }
        .circle-status { background-color: #fae8ff; color: #c084fc; }
        .circle-admin { background-color: #e0e7ff; color: #4f46e5; }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--nsti-blue);
            margin-bottom: 0.5rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .card-description {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.45;
            margin: 0;
        }

        /* Committee Section styling */
        .committee-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-top: 1rem;
        }

        @media (max-width: 991px) {
            .committee-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .committee-grid {
                grid-template-columns: 1fr;
            }
        }

        .committee-card {
            background-color: #ffffff;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            padding: 3rem 1.5rem 2.5rem 1.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .committee-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(to right, var(--nsti-magenta), #ff758c);
            transform: scaleX(0);
            transition: transform 0.4s ease;
            transform-origin: center;
        }

        .committee-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.1);
        }

        .committee-card:hover::after {
            transform: scaleX(1);
        }

        .committee-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background-color: #f8fafc;
            color: var(--nsti-blue);
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 1.5rem auto;
            font-size: 2rem;
            border: 4px solid #ffffff;
            box-shadow: 0 12px 35px rgba(15, 48, 87, 0.12);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            position: relative;
            z-index: 2;
        }

        .committee-card:hover .committee-avatar {
            transform: scale(1.1) translateY(-5px);
            box-shadow: 0 15px 40px rgba(176, 28, 112, 0.2);
            border-color: #fff0f5;
        }

        .committee-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 12%;
            border-radius: 50%;
        }

        .committee-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--nsti-blue);
            margin: 0;
        }

        .committee-role {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--nsti-magenta);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.25rem;
        }

        .committee-designation {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0;
            margin-bottom: 0;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease, margin-top 0.4s ease;
        }

        .committee-card.show-description .committee-designation {
            max-height: 120px;
            opacity: 1;
            margin-top: 0.75rem;
        }

        .toggle-icon {
            font-size: 0.75rem;
            margin-left: 6px;
            transition: transform 0.3s ease;
            vertical-align: middle;
            opacity: 0.75;
        }

        .committee-card.show-description .toggle-icon {
            transform: rotate(180deg);
        }

        /* Contact Details Card Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 991px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        .contact-box {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid var(--card-border);
            padding: 2rem 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            height: 100%;
        }

        .contact-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background-color: #eff6ff;
            color: #2563eb;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
            font-size: 1.25rem;
        }

        .contact-box.magenta-theme .contact-icon-wrapper {
            background-color: #fdf2f8;
            color: #db2777;
        }

        .contact-box.green-theme .contact-icon-wrapper {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .contact-info-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--nsti-blue);
            margin-bottom: 0.5rem;
        }

        .contact-info-text {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0;
        }

        /* Footer Branding Watermark */
        .footer-watermark {
            text-align: center;
            padding: 2rem 0;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
            width: 100%;
        }

        .watermark-text {
            font-size: 0.8rem;
            font-weight: 700;
            color: #cbd5e1;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
            display: inline-block;
            position: relative;
        }

        .watermark-text::before, .watermark-text::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30px;
            height: 1px;
            background-color: #e2e8f0;
        }

        .watermark-text::before { right: 100%; margin-right: 15px; }
        .watermark-text::after { left: 100%; margin-left: 15px; }

        /* Dynamic site footer styles to group About and elements below it */
        .site-footer {
            background-color: #0a2240;
            color: #ffffff;
            margin-top: 5rem;
            padding-top: 3rem;
            border-top: 5px solid var(--nsti-magenta);
            position: relative;
            z-index: 1;
        }
        .site-footer .page-section {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .site-footer .section-title {
            color: #ffffff;
        }
        .site-footer .section-decor-dots span {
            background-color: rgba(255, 255, 255, 0.25);
        }
        .site-footer .section-decor-dots span:first-child {
            background-color: var(--nsti-magenta);
        }
        .site-footer .about-text {
            color: #cbd5e1;
        }
        .site-footer .section-card,
        .site-footer .vision-card,
        .site-footer .mission-card,
        .site-footer .committee-card,
        .site-footer .contact-box {
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .site-footer .section-card:hover,
        .site-footer .vision-card:hover,
        .site-footer .mission-card:hover,
        .site-footer .committee-card:hover,
        .site-footer .contact-box:hover {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        .site-footer .vm-title,
        .site-footer .committee-name,
        .site-footer .contact-info-title {
            color: #ffffff;
        }
        .site-footer .vm-desc,
        .site-footer .committee-designation,
        .site-footer .contact-info-text {
            color: #cbd5e1;
        }
        .site-footer .committee-avatar {
            background-color: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .site-footer .footer-watermark {
            text-align: center;
            padding: 2rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background-color: transparent;
            width: 100%;
            margin-top: 2rem;
        }
        .site-footer .watermark-text {
            color: rgba(255, 255, 255, 0.25);
        }
        .site-footer .watermark-text::before, 
        .site-footer .watermark-text::after {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* 4-column Footer Links Grid styling matching mockup */
        .footer-links-grid {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: 3rem;
            padding-top: 4rem;
            padding-bottom: 2rem;
        }
        .footer-grid-title {
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            position: relative;
            padding-bottom: 10px;
        }
        .footer-grid-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 2px;
            background-color: var(--nsti-magenta);
        }
        .footer-grid-text {
            font-size: 0.9rem;
            line-height: 1.6;
            color: #cbd5e1;
        }
        .text-secondary-white {
            color: #cbd5e1 !important;
            font-size: 0.9rem;
        }
        .footer-link {
            color: #cbd5e1;
            font-size: 0.9rem;
            transition: color 0.2s ease, padding-left 0.2s ease;
        }
        .footer-link:hover {
            color: #ffe600;
            padding-left: 5px;
        }
        .footer-contact-list i {
            color: var(--nsti-magenta);
            width: 20px;
        }
        .footer-links-list li i {
            color: var(--nsti-magenta);
            font-size: 0.85rem;
            width: 20px;
            text-align: center;
            display: inline-block;
        }
        .site-footer .text-muted {
            color: #94a3b8 !important;
        }
        
        /* Hero Carousel Section */
        .hero-carousel-section {
            width: 100%;
            overflow: hidden;
            background-color: #f7f9fc;
        }
        .hero-banner-container {
            position: relative;
            width: 100%;
            height: 480px;
            background-color: #0f3057;
            overflow: hidden;
        }
        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.8s ease-in-out, visibility 0.8s ease-in-out;
            z-index: 1;
        }
        .hero-slide.active {
            opacity: 1;
            visibility: visible;
            z-index: 2;
        }
        .hero-text-wrapper {
            padding-left: 5rem;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.25);
            user-select: none;
        }
        .hero-title-orange {
            font-size: 2.2rem;
            font-weight: 800;
            color: #f39c12; /* Warm orange matching mockup */
            margin: 0;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .hero-title-white {
            font-size: 3.8rem;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .hero-title-books {
            font-size: 3.4rem;
            font-weight: 800;
            background: linear-gradient(to right, #00f2fe, #f72585);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0px 0px 15px rgba(247, 37, 133, 0.4);
            margin: 0;
            line-height: 1.1;
            text-transform: none;
        }
        /* â”€â”€ NSTI Building Slide: SPLIT LAYOUT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
           LEFT  (40%): Dark navy branding panel with text & buttons
           RIGHT (60%): 100% clear HD building photo â€” zero overlay
        â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .hero-slide-nsti {
            display: flex !important;
            flex-direction: row;
            align-items: stretch;
            overflow: hidden;
            background: #0a1020;
        }
        /* â”€â”€ LEFT PANEL â”€â”€ */
        .nsti-left-panel {
            width: 42%;
            flex-shrink: 0;
            background: linear-gradient(160deg, #0b1f45 0%, #0f3057 55%, #1a0a2e 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.8rem 3rem 2.8rem 3.5rem;
            position: relative;
            z-index: 2;
            box-shadow: 6px 0 30px rgba(0,0,0,0.45);
        }
        /* Decorative accent line on left panel */
        .nsti-left-panel::before {
            content: '';
            position: absolute;
            left: 0; top: 15%; bottom: 15%;
            width: 4px;
            background: linear-gradient(to bottom, #ffe600, #b01c70);
            border-radius: 0 4px 4px 0;
        }
        .nsti-left-panel::after {
            content: '';
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 60px;
            background: linear-gradient(to right, transparent, rgba(0,0,0,0.18));
            pointer-events: none;
        }
        .hero-nsti-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255,230,0,0.1);
            border: 1px solid rgba(255,230,0,0.5);
            border-radius: 50px;
            color: #ffe600;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 0.28rem 1rem;
            margin-bottom: 1rem;
            width: fit-content;
        }
        .hero-nsti-title {
            font-size: 2.15rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 0.75rem;
        }
        .hero-nsti-title span { color: #ffe600; }
        .hero-nsti-divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, #b01c70, #ffe600);
            border-radius: 2px;
            margin-bottom: 0.9rem;
        }
        .hero-nsti-sub {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.72);
            font-weight: 400;
            line-height: 1.65;
            margin-bottom: 1.6rem;
        }
        .hero-nsti-btns {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            align-items: flex-start;
        }
        .btn-nsti-primary {
            background: linear-gradient(135deg, #b01c70, #7c1049);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 0.7rem 1.8rem;
            font-weight: 700;
            font-size: 0.88rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 18px rgba(176,28,112,0.45);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        .btn-nsti-primary:hover {
            transform: translateX(4px);
            box-shadow: 0 6px 24px rgba(176,28,112,0.65);
            color: #fff;
        }
        .btn-nsti-outline {
            background: transparent;
            color: rgba(255,255,255,0.85);
            border: 1.5px solid rgba(255,255,255,0.35);
            border-radius: 50px;
            padding: 0.68rem 1.8rem;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        .btn-nsti-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.7);
            color: #fff;
            transform: translateX(4px);
        }
        /* â”€â”€ RIGHT PANEL: Pure HD photo, ZERO overlay â”€â”€ */
        .nsti-right-panel {
            width: 58%;
            flex-shrink: 1;
            position: relative;
            overflow: hidden;
        }
        .nsti-right-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            display: block;
        }
        /* Responsive */
        @media (max-width: 900px) {
            .hero-slide-nsti { flex-direction: column; }
            .nsti-left-panel {
                width: 100%;
                padding: 2rem 1.5rem 1.5rem 2rem;
            }
            .nsti-right-panel { width: 100%; height: 220px; }
            .hero-nsti-title { font-size: 1.55rem; }
            .hero-nsti-btns { flex-direction: row; }
        }
        .hero-title-dark-blue {
            font-size: 3.8rem;
            font-weight: 800;
            color: #0f3057; /* Deep blue matching NSTI branding */
            margin: 0;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .hero-title-magenta {
            font-size: 3.8rem;
            font-weight: 800;
            color: #b01c70; /* Magenta matching NSTI branding */
            margin: 0;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .custom-section-header-container {
            display: flex;
            justify-content: center;
            width: 100%;
            margin-top: 1rem;
            margin-bottom: 3rem;
        }
        .custom-section-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .header-left-bar {
            width: 6px;
            height: 75px;
            background: linear-gradient(to bottom, #ff4e3a, #e61c5d); /* Red to pink gradient */
            border-radius: 3px;
        }
        .header-content-wrapper {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .header-title-text {
            font-size: 3.8rem;
            font-weight: 800;
            color: #0f3057; /* Deep blue NSTI */
            line-height: 1.25;
            margin: 0;
            text-transform: none;
        }
        .header-bottom-line {
            width: 180px;
            height: 4px;
            background-color: #3b82f6; /* Blue underline */
            margin-top: 10px;
            border-radius: 2px;
        }
        .carousel-control {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.5rem;
            padding: 1rem;
            cursor: pointer;
            z-index: 10;
            transition: color 0.2s;
        }
        .carousel-control:hover {
            color: #ffffff;
        }
        .prev-control {
            left: 20px;
        }
        .next-control {
            right: 20px;
        }
        .carousel-indicators-dots {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }
        .carousel-indicators-dots .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .carousel-indicators-dots .dot.active {
            background-color: #ffffff;
        }
        @media (max-width: 991px) {
            .hero-text-wrapper {
                padding-left: 2rem;
            }
            .hero-title-orange, .hero-title-white, .hero-title-dark-blue, .hero-title-magenta {
                font-size: 2.8rem;
            }
            .hero-title-books {
                font-size: 2.5rem;
            }
        }
        @media (max-width: 768px) {
            .hero-banner-container {
                height: 280px;
            }
            .hero-title-orange, .hero-title-white, .hero-title-dark-blue, .hero-title-magenta {
                font-size: 2.2rem;
            }
            .hero-title-books {
                font-size: 1.8rem;
            }
            .hero-text-wrapper {
                padding-left: 1rem;
            }
            .carousel-control {
                font-size: 1.1rem;
                padding: 0.5rem;
            }
            .header-title-text {
                font-size: 1.6rem;
            }
            .header-left-bar {
                height: 55px;
            }
            .header-bottom-line {
                width: 130px;
                height: 3px;
            }
        }

        /* Steps timeline styles */
        .steps-timeline-container {
            position: relative;
            width: 100%;
            max-width: 1100px;
            height: 380px;
            margin: 0 auto;
        }

        .steps-wave-path {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .steps-wave-path path {
            stroke-width: 4;
            stroke-dasharray: 10, 10;
            fill: none;
            animation: dash-flow 45s linear infinite;
        }

        @keyframes dash-flow {
            to {
                stroke-dashoffset: -1000;
            }
        }

        .steps-row {
            display: flex;
            position: relative;
            z-index: 2;
            height: 100%;
            width: 100%;
        }

        .step-item {
            position: relative;
            width: 25%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0 1rem;
        }

        .step-item.purple-theme { --theme-color: #9333ea; --theme-bg-light: #f3e8ff; }
        .step-item.orange-theme { --theme-color: #ea580c; --theme-bg-light: #ffedd5; }
        .step-item.teal-theme { --theme-color: #0d9488; --theme-bg-light: #ccfbf1; }
        .step-item.pink-theme { --theme-color: #db2777; --theme-bg-light: #fce7f3; }

        .step-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            padding: 1.25rem 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
            position: absolute;
            width: 90%;
            z-index: 5;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step-item:hover .step-card {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
            border-color: var(--theme-color);
        }

        /* Speech bubble arrows pointing to wave centers */
        .step-item.valley .step-card {
            top: 0;
        }
        .step-item.valley .step-card::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            width: 16px;
            height: 16px;
            background: #ffffff;
            border-right: 1px solid rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .step-item.valley:hover .step-card::after {
            border-right-color: var(--theme-color);
            border-bottom-color: var(--theme-color);
        }

        .step-item.peak .step-card {
            bottom: 0;
        }
        .step-item.peak .step-card::after {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            width: 16px;
            height: 16px;
            background: #ffffff;
            border-left: 1px solid rgba(0, 0, 0, 0.08);
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .step-item.peak:hover .step-card::after {
            border-left-color: var(--theme-color);
            border-top-color: var(--theme-color);
        }

        .step-circle {
            position: absolute;
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.6rem;
            z-index: 4;
            border: 4px solid var(--theme-color);
            color: var(--theme-color);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            left: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step-item:hover .step-circle {
            transform: translate(-50%, -50%) scale(1.12);
            background-color: var(--theme-color);
            color: #ffffff;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.18);
        }

        .step-number {
            position: absolute;
            font-weight: 800;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--theme-color);
            background-color: var(--theme-bg-light);
            padding: 0.35rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            z-index: 3;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .step-item:hover .step-number {
            transform: translateX(-50%) scale(1.08);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }

        .step-desc {
            font-size: 0.95rem;
            color: var(--text-dark);
            line-height: 1.6;
            margin: 0;
            font-weight: 500;
        }

        /* Valleys: Steps 1 & 3 */
        .step-item.valley .step-circle {
            top: 80%;
        }
        .step-item.valley .step-number {
            top: 50%;
        }

        /* Peaks: Steps 2 & 4 */
        .step-item.peak .step-circle {
            top: 20%;
        }
        .step-item.peak .step-number {
            top: 42%;
        }

        /* Responsive timeline */
        @media (max-width: 991px) {
            .steps-timeline-container {
                height: auto;
                max-width: 600px;
                margin: 0 auto;
                padding: 0;
            }

            .steps-wave-path {
                display: none;
            }

            .steps-row {
                flex-direction: column;
                gap: 2.5rem;
                position: relative;
            }

            .steps-row::before {
                content: '';
                position: absolute;
                left: 38px;
                top: 20px;
                bottom: 20px;
                width: 3px;
                border-left: 3px dashed #cbd5e1;
                z-index: 1;
            }

            .step-item {
                display: grid;
                grid-template-columns: 76px 1fr;
                grid-template-rows: auto auto;
                grid-template-areas: 
                    "circle number"
                    "circle desc";
                align-items: center;
                gap: 0.25rem 1.5rem;
                width: 100%;
                text-align: left;
                padding: 0;
                min-height: 80px;
            }

            .step-card {
                grid-area: desc;
                position: relative;
                width: 100%;
                background: transparent;
                border: none;
                box-shadow: none;
                padding: 0;
                top: auto !important;
                bottom: auto !important;
                left: auto !important;
                transform: none !important;
            }

            .step-card::after {
                display: none;
            }

            .step-item:hover .step-card {
                transform: none !important;
                box-shadow: none !important;
            }

            .step-circle {
                grid-area: circle;
                position: relative;
                left: auto !important;
                top: auto !important;
                transform: none !important;
                z-index: 2;
            }

            .step-item:hover .step-circle {
                transform: scale(1.1) !important;
            }

            .step-number {
                grid-area: number;
                position: relative;
                left: auto !important;
                top: auto !important;
                transform: none !important;
                margin: 0;
                font-size: 1.15rem;
            }

            .step-item:hover .step-number {
                transform: none !important;
            }

            .step-desc {
                font-size: 0.9rem;
            }
        }
        /* Premium Status Tracker Card */
        .status-tracker-card {
            background: linear-gradient(135deg, #0f3057 0%, #b01c70 100%);
            border-radius: 20px;
            padding: 3rem 2.5rem;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 12px 30px rgba(176, 28, 112, 0.15);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .status-tracker-card::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255, 230, 0, 0.15) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.5s ease;
            z-index: -1;
            pointer-events: none;
        }
        .status-tracker-card:hover::before { opacity: 1; }
        .status-tracker-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 45px rgba(176, 28, 112, 0.35);
            border-color: rgba(255, 230, 0, 0.4);
        }
        .status-tracker-card .icon-wrapper-glowing {
            width: 80px; height: 80px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.25);
            display: flex; justify-content: center; align-items: center;
            margin-bottom: 1.5rem;
            color: #ffe600;
            box-shadow: 0 8px 25px rgba(255, 230, 0, 0.15);
            transition: all 0.4s ease;
        }
        .status-tracker-card:hover .icon-wrapper-glowing {
            background-color: #ffe600; color: #0f3057;
            border-color: #ffe600;
            box-shadow: 0 10px 30px rgba(255, 230, 0, 0.45);
            transform: scale(1.1) rotate(10deg);
        }
        .status-tracker-card .tracker-title {
            font-size: 1.45rem; font-weight: 800; color: #ffffff;
            margin-bottom: 0.6rem; letter-spacing: 0.5px;
            text-transform: uppercase; transition: color 0.3s ease;
        }
        .status-tracker-card:hover .tracker-title { color: #ffe600; }
        .status-tracker-card .tracker-description {
            font-size: 0.95rem; color: #cbd5e1;
            line-height: 1.6; margin-bottom: 1.5rem; max-width: 480px;
        }
        .status-tracker-card .tracker-btn {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            color: #ffffff; font-size: 0.9rem; font-weight: 700;
            padding: 0.6rem 2.2rem; border-radius: 30px;
            transition: all 0.3s ease;
            display: inline-flex; align-items: center; gap: 0.5rem;
        }
        .status-tracker-card:hover .tracker-btn {
            background-color: #ffffff; color: #0f3057;
            border-color: #ffffff;
            box-shadow: 0 8px 20px rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        /* Orange Track Grievances Banner */
        .orange-track-banner {
            background-color: #e05e19;
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 100px; /* Fully rounded ends like a pill */
            box-shadow: 0 8px 30px rgba(224, 94, 25, 0.2);
            width: 100%;
            max-width: 550px;
            margin: 0 auto;
            border: 1.5px solid rgba(255, 255, 255, 0.1);
        }
        .btn-track-old {
            background-color: #0f3057;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 1.1rem;
            border: 2.5px solid #ffffff;
            border-radius: 50px; /* Fully rounded blue pill */
            padding: 0.65rem 2.8rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(15, 48, 87, 0.4);
            text-transform: none;
            letter-spacing: 0.3px;
        }
        .btn-track-old:hover {
            background-color: #0b223f;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(15, 48, 87, 0.6);
        }

        /* Committee Marquee Styling */
        .committee-marquee-container {
            overflow: hidden;
            width: 100%;
            position: relative;
            padding: 1.5rem 0;
        }

        .committee-marquee-track {
            display: flex;
            width: max-content;
            animation: scroll-right-to-left 20s linear infinite;
        }

        .committee-marquee-group {
            display: flex;
            gap: 1.5rem;
            padding-right: 1.5rem;
        }

        .committee-marquee-group .committee-card {
            width: 270px;
            flex-shrink: 0;
        }

        /* Pause animation on hover */
        .committee-marquee-container:hover .committee-marquee-track {
            animation-play-state: paused;
        }

        @keyframes scroll-right-to-left {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        /* Latest News Ticker styling */
        .news-ticker-bar {
            background-color: #ffffff;
            border-bottom: 2px solid var(--nsti-magenta);
            height: 42px;
            display: flex;
            align-items: center;
            overflow: hidden;
            font-family: 'Outfit', sans-serif;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .news-ticker-label {
            background-color: var(--nsti-magenta);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            z-index: 10;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.15);
            white-space: nowrap;
        }
        .news-ticker-content {
            flex-grow: 1;
            overflow: hidden;
            display: flex;
            align-items: center;
            height: 100%;
            background-color: #fcfdfe;
        }
        .news-ticker-content marquee {
            height: 100%;
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .news-ticker-item {
            margin-right: 4rem;
            font-weight: 500;
            color: var(--nsti-blue);
            font-size: 0.92rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        .news-ticker-item i {
            color: var(--nsti-magenta);
            font-size: 0.95rem;
        }

        /* ===== Premium Rating Widget ===== */
        .footer-rating-section {
            padding: 2.5rem 1.5rem;
            background: transparent;
            border: none;
            position: relative;
            overflow: hidden;
        }
        .footer-rating-section::before {
            content: '';
            position: absolute;
            top: -60px; left: 50%; transform: translateX(-50%);
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(251,191,36,0.04) 0%, transparent 70%);
            pointer-events: none;
        }
        .footer-rating-inner {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .footer-rating-heading {
            font-size: 1.25rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .footer-rating-heading .star-icon-glow {
            color: #fbbf24;
            filter: drop-shadow(0 0 6px rgba(251,191,36,0.6));
            font-size: 1.1rem;
        }
        .footer-rating-tagline {
            font-size: 0.82rem;
            color: #94a3b8;
            margin-bottom: 1.4rem;
        }
        /* Stars Row */
        .star-rating-group {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 6px;
            margin-bottom: 1.2rem;
        }
        .star-rating-group input[type="radio"] { display: none; }
        .star-rating-group label {
            font-size: 2.4rem;
            color: rgba(255,255,255,0.15);
            cursor: pointer;
            transition: color 0.18s ease, transform 0.2s cubic-bezier(0.34,1.56,0.64,1), filter 0.18s ease;
            line-height: 1;
            user-select: none;
        }
        .star-rating-group label:hover,
        .star-rating-group label:hover ~ label,
        .star-rating-group input[type="radio"]:checked ~ label {
            color: #fbbf24;
            filter: drop-shadow(0 0 5px rgba(251,191,36,0.5));
            transform: scale(1.22);
        }
        .star-rating-group label:hover { transform: scale(1.35); }
        /* Bottom row: submit + avg */
        .rating-bottom-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .rating-submit-btn {
            background: linear-gradient(135deg, #b01c70, #7c3aed);
            color: #ffffff;
            border: none;
            border-radius: 30px;
            padding: 0.5rem 1.8rem;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: none;
            letter-spacing: 0.4px;
            box-shadow: 0 4px 15px rgba(176,28,112,0.3);
        }
        .rating-submit-btn:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 8px 25px rgba(176,28,112,0.5);
        }
        .rating-avg-display {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(251,191,36,0.08);
            border: 1px solid rgba(251,191,36,0.2);
            border-radius: 30px;
            padding: 0.4rem 1.2rem;
        }
        .rating-avg-score {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fbbf24;
        }
        .rating-avg-stars {
            font-size: 0.88rem;
            color: #fbbf24;
            letter-spacing: 2px;
        }
        .rating-avg-count {
            font-size: 0.75rem;
            color: #94a3b8;
            white-space: nowrap;
        }
        /* Toast */
        .rating-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #0f3057;
            color: #ffffff;
            border-left: 4px solid #fbbf24;
            border-radius: 12px;
            padding: 1rem 1.6rem;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transform: translateY(120%);
            opacity: 0;
            transition: transform 0.45s cubic-bezier(0.16,1,0.3,1), opacity 0.45s ease;
        }
        .rating-toast.show { transform: translateY(0); opacity: 1; }
        @media (max-width: 576px) {
            .star-rating-group label { font-size: 2rem; }
            .footer-rating-heading { font-size: 1.05rem; }
        }
    </style>
    <!-- Accessibility Styles & Script -->
    <link rel="stylesheet" href="assets/css/accessibility.css">
    <script src="assets/js/accessibility.js" defer></script>
</head>
<body id="home">
    <?php include_once 'includes/accessibility_bar.php'; ?>

    <!-- Top Header Branding Section matching user requested logo/title alignment -->
    <header class="header-branding">
        <a href="index.php" class="header-logo">
            <img src="assets/images/nsti_logo.png?v=2" alt="NSTI Logo" width="110" height="110">
            <p class="logo-caption">National Skill Training Institute<span class="caption-location"> - Ramanthapur</span></p>
        </a>
        <div class="header-titles">
            <h1 class="main-title">NSTI R-HYDERABAD</h1>
            <h2 class="sub-title">Online Grievance Redressal System</h2>
        </div>
        <!-- Spacer to center title correctly on desktop -->
        <div class="header-right-logo d-none d-lg-flex">
            <img src="assets/images/committee_illustration.png" alt="Committee Illustration" style="height: 120px; width: auto; object-fit: contain;">
        </div>
    </header>

    <!-- Horizontal Navbar matching the blue design and scroll links -->
    <nav class="custom-navbar">
        <div class="container navbar-container">
            <a href="index.php" class="mobile-brand">NSTI R-HYDERABAD</a>
            <button class="menu-toggle" id="mobile-menu-toggle" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="#home" class="active"><span class="notranslate custom-translate-home">Home</span></a></li>
                <li class="separator">|</li>
                <li><a href="about.php">About</a></li>
                <li class="separator">|</li>
                <li><a href="contact.php">Contact</a></li>
                <li class="separator">|</li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" id="grievanceDropdown" role="button" aria-expanded="false">
                        Help & Support <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="grievanceDropdown">
                        <li><a class="dropdown-item" href="check_status.php">Track Status</a></li>
                        <li><a class="dropdown-item" href="steps_to_lodge.php">Steps to Lodge</a></li>
                        <li><a class="dropdown-item" href="team_members.php">Project Team</a></li>
                    </ul>
                </li>
                <li class="separator">|</li>
                <li><a href="feedback.php">Feedback</a></li>
                <li class="separator">|</li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Content -->

    <!-- Latest News Ticker Bar -->
    <div class="news-ticker-bar" role="region" aria-label="Latest News">
        <div class="news-ticker-label">
            <i class="fas fa-bullhorn me-2"></i> Latest News
        </div>
        <div class="news-ticker-content">
            <marquee behavior="scroll" direction="left" scrollamount="5" onmouseover="this.stop();" onmouseout="this.start();">
                <span class="news-ticker-item"><i class="fas fa-circle-dot"></i> Online Grievance Redressal System is now live for Students, Staff & Parents of NSTI R-Hyderabad.</span>
                <span class="news-ticker-item"><i class="fas fa-circle-dot"></i> Submit your grievance online 24Ã—7 â€” fast, secure, and fully transparent.</span>
                <span class="news-ticker-item"><i class="fas fa-circle-dot"></i> Track the real-time status of your submitted grievance using your unique Grievance ID.</span>
                <span class="news-ticker-item"><i class="fas fa-circle-dot"></i> All grievances are addressed within 7 working days by the designated Grievance Committee.</span>
                <span class="news-ticker-item"><i class="fas fa-circle-dot"></i> Register now to lodge your grievance â€” Student, Parent, and Employee portals are available.</span>
                <span class="news-ticker-item"><i class="fas fa-circle-dot"></i> Your feedback helps us improve â€” please share your experience after grievance resolution.</span>
                <span class="news-ticker-item"><i class="fas fa-circle-dot"></i> Grievance Committee members are available for in-person queries during office hours.</span>
                <span class="news-ticker-item"><i class="fas fa-circle-dot"></i> For urgent matters, contact the NSTI R-Hyderabad administration directly via the Contact page.</span>
            </marquee>
        </div>
    </div>

    <!-- Hero Slider Banner Section -->
    <section class="hero-carousel-section" id="home-hero">
        <div class="hero-banner-container">
            <!-- Navigation controls -->
            <button class="carousel-control prev-control" id="prev-slide"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-control next-control" id="next-slide"><i class="fas fa-chevron-right"></i></button>

            <!-- Slide 1: NSTI Building â€” Split Layout (Left: Branding | Right: HD Photo) -->
            <div class="hero-slide hero-slide-nsti active">

                <!-- LEFT: Dark navy branding panel -->
                <div class="nsti-left-panel">
                    <div class="hero-nsti-badge">
                        <i class="fas fa-map-marker-alt"></i> NSTI Ramanthapur, Hyderabad
                    </div>
                    <h1 class="hero-nsti-title">
                        Welcome to <span>NSTI</span><br>Online Grievance Cell
                    </h1>
                    <div class="hero-nsti-divider"></div>
                    <p class="hero-nsti-sub">
                        A transparent digital platform to lodge, track &amp; resolve your grievances â€” quickly and efficiently.
                    </p>
                    <div class="hero-nsti-btns">
                        <a href="submit_grievance.php" class="btn-nsti-primary">
                            <i class="fas fa-pen"></i> Lodge a Grievance
                        </a>
                        <a href="check_status.php" class="btn-nsti-outline">
                            <i class="fas fa-search"></i> Track Grievance Status
                        </a>
                    </div>
                </div>

                <!-- RIGHT: 100% Clear HD Building Photo â€” Zero overlay -->
                <div class="nsti-right-panel">
                    <img src="assets/images/nsti_building.png" alt="NSTI Campus, Ramanthapur Hyderabad" />
                </div>

            </div>

            <!-- Slide 2: Books Banner replaced with Grievance Keyboard Banner -->
            <div class="hero-slide" style="background-image: url('assets/images/online_grievance_banner.png');">
                <div class="container d-flex align-items-center h-100">
                    <div class="row w-100">
                        <div class="col-md-10 ps-md-5">
                            <div class="hero-text-wrapper text-start">
                                <h2 class="hero-title-books">Grievance Redressal System</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3: Keyboard Banner -->
            <div class="hero-slide" style="background-image: url('assets/images/keyboard_banner.png');">
                <div class="container d-flex align-items-center h-100">
                    <div class="row w-100">
                        <div class="col-md-7 ps-md-5">
                            <div class="hero-text-wrapper text-start">
                                <h2 class="hero-title-dark-blue">Lodge</h2>
                                <h2 class="hero-title-magenta">Grievance</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide Indicators -->
            <div class="carousel-indicators-dots">
                <span class="dot active" data-slide="0"></span>
                <span class="dot" data-slide="1"></span>
                <span class="dot" data-slide="2"></span>
            </div>
        </div>
    </section>


    <?php if (isLoggedIn()): ?>
    <!-- My Complaints Summary Strip (visible only when logged in) -->
    <section style="background: linear-gradient(135deg, #0f3057 0%, #1a4a80 50%, #b01c70 100%); padding: 1.5rem 0; position: relative; overflow: hidden;">
        <!-- Background pattern -->
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><circle cx=%2230%22 cy=%2230%22 r=%2220%22 fill=%22none%22 stroke=%22rgba(255,255,255,0.04)%22 stroke-width=%222%22/></svg>'); opacity:0.8;"></div>
        <div class="container" style="position:relative;z-index:2;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Greeting -->
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-user-circle" style="color:#fff;font-size:1.3rem;"></i>
                    </div>
                    <div>
                        <p style="color:rgba(255,255,255,0.75);font-size:0.75rem;margin:0;font-weight:600;text-transform:uppercase;letter-spacing:1px;">My Complaints Summary</p>
                        <p style="color:#fff;font-size:1rem;margin:0;font-weight:700;"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></p>
                    </div>
                </div>
                <!-- Stats -->
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <!-- Total (with View button) -->
                    <div style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:16px;padding:0.75rem 1.4rem;min-width:130px;text-align:center;">
                        <p style="color:rgba(255,255,255,0.7);font-size:0.65rem;margin:0;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;">Total Complaints</p>
                        <p style="color:#fff;font-size:2rem;margin:0;font-weight:800;line-height:1.1;"><?php echo $hp_total; ?></p>
                        <button onclick="document.getElementById('complaintsModal').style.display='flex'" style="margin-top:0.4rem;background:rgba(255,255,255,0.25);border:1px solid rgba(255,255,255,0.5);color:#fff;font-size:0.7rem;font-weight:700;padding:0.25rem 0.85rem;border-radius:50px;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.4)'" onmouseout="this.style.background='rgba(255,255,255,0.25)'">
                            <i class="fas fa-eye"></i> View All
                        </button>
                    </div>
                    <!-- Pending -->
                    <div style="background:rgba(251,146,60,0.2);border:1px solid rgba(251,146,60,0.4);border-radius:16px;padding:0.75rem 1.4rem;min-width:110px;text-align:center;">
                        <p style="color:rgba(255,255,255,0.7);font-size:0.65rem;margin:0;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;"><i class="fas fa-clock" style="font-size:0.6rem;"></i> Pending</p>
                        <p style="color:#fbbf24;font-size:2rem;margin:0;font-weight:800;line-height:1.1;"><?php echo $hp_pending; ?></p>
                    </div>
                    <!-- In Process -->
                    <div style="background:rgba(56,189,248,0.2);border:1px solid rgba(56,189,248,0.35);border-radius:16px;padding:0.75rem 1.4rem;min-width:110px;text-align:center;">
                        <p style="color:rgba(255,255,255,0.7);font-size:0.65rem;margin:0;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;"><i class="fas fa-spinner" style="font-size:0.6rem;"></i> In Process</p>
                        <p style="color:#38bdf8;font-size:2rem;margin:0;font-weight:800;line-height:1.1;"><?php echo $hp_inprocess; ?></p>
                    </div>
                    <!-- Closed -->
                    <div style="background:rgba(74,222,128,0.15);border:1px solid rgba(74,222,128,0.3);border-radius:16px;padding:0.75rem 1.4rem;min-width:110px;text-align:center;">
                        <p style="color:rgba(255,255,255,0.7);font-size:0.65rem;margin:0;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;"><i class="fas fa-check-circle" style="font-size:0.6rem;"></i> Closed</p>
                        <p style="color:#4ade80;font-size:2rem;margin:0;font-weight:800;line-height:1.1;"><?php echo $hp_closed; ?></p>
                    </div>
                </div>
                <!-- Action Button -->
                <a href="user/dashboard.php" style="background:rgba(255,255,255,0.15);border:1.5px solid rgba(255,255,255,0.4);color:#fff;text-decoration:none;padding:0.65rem 1.4rem;border-radius:50px;font-size:0.82rem;font-weight:700;display:flex;align-items:center;gap:0.5rem;transition:all 0.3s;white-space: nowrap;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <i class="fas fa-tachometer-alt"></i> My Dashboard
                </a>
            </div>
        </div>
    </section>

    <!-- Complaints Modal -->
    <div id="complaintsModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
        <div style="background:#fff;border-radius:24px;width:90%;max-width:800px;max-height:85vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,0.3);">
            <!-- Modal Header -->
            <div style="background:linear-gradient(135deg,#0f3057,#b01c70);padding:1.5rem 2rem;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <h5 style="color:#fff;margin:0;font-weight:800;font-size:1.2rem;"><i class="fas fa-list-alt" style="margin-right:0.5rem;"></i>My All Complaints</h5>
                    <p style="color:rgba(255,255,255,0.7);margin:0;font-size:0.8rem;">Total: <?php echo $hp_total; ?> complaint(s)</p>
                </div>
                <button onclick="document.getElementById('complaintsModal').style.display='none'" style="background:rgba(255,255,255,0.2);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <!-- Modal Body -->
            <div style="overflow-y:auto;flex:1;padding:1.5rem 2rem;">
                <?php if (empty($hp_complaints)): ?>
                    <div style="text-align:center;padding:3rem 0;color:#94a3b8;">
                        <i class="fas fa-inbox" style="font-size:3rem;margin-bottom:1rem;display:block;"></i>
                        <p style="font-size:1rem;font-weight:600;">No complaints submitted yet.</p>
                        <a href="submit_grievance.php" style="background:#0f3057;color:#fff;text-decoration:none;padding:0.6rem 1.5rem;border-radius:50px;font-size:0.85rem;font-weight:700;">Lodge a Grievance</a>
                    </div>
                <?php else: ?>
                    <table style="width:100%;border-collapse:collapse;font-family:'Outfit',sans-serif;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th style="padding:0.85rem 1rem;text-align:left;font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">#</th>
                                <th style="padding:0.85rem 1rem;text-align:left;font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Subject</th>
                                <th style="padding:0.85rem 1rem;text-align:left;font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Category</th>
                                <th style="padding:0.85rem 1rem;text-align:left;font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Date</th>
                                <th style="padding:0.85rem 1rem;text-align:center;font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hp_complaints as $i => $c):
                                $statusColors = [
                                    'Pending'     => ['bg'=>'#fff7ed','color'=>'#ea580c','border'=>'#fed7aa'],
                                    'In Progress' => ['bg'=>'#eff6ff','color'=>'#2563eb','border'=>'#bfdbfe'],
                                    'Resolved'    => ['bg'=>'#f0fdf4','color'=>'#16a34a','border'=>'#bbf7d0'],
                                    'Rejected'    => ['bg'=>'#fef2f2','color'=>'#dc2626','border'=>'#fecaca'],
                                ];
                                $sc = $statusColors[$c['status']] ?? ['bg'=>'#f8fafc','color'=>'#64748b','border'=>'#e2e8f0'];
                            ?>
                            <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding:0.9rem 1rem;font-size:0.82rem;color:#94a3b8;font-weight:600;"><?php echo $i+1; ?></td>
                                <td style="padding:0.9rem 1rem;font-size:0.88rem;font-weight:600;color:#1e293b;max-width:220px;">
                                    <span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space: nowrap;" title="<?php echo htmlspecialchars($c['subject']); ?>">
                                        <?php echo htmlspecialchars($c['subject']); ?>
                                    </span>
                                </td>
                                <td style="padding:0.9rem 1rem;font-size:0.82rem;color:#475569;"><?php echo htmlspecialchars($c['category']); ?></td>
                                <td style="padding:0.9rem 1rem;font-size:0.82rem;color:#64748b;white-space: nowrap;"><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                                <td style="padding:0.9rem 1rem;text-align:center;">
                                    <span style="background:<?php echo $sc['bg']; ?>;color:<?php echo $sc['color']; ?>;border:1px solid <?php echo $sc['border']; ?>;padding:0.25rem 0.8rem;border-radius:50px;font-size:0.72rem;font-weight:700;">
                                        <?php echo htmlspecialchars($c['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <!-- Modal Footer -->
            <div style="padding:1rem 2rem;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:0.75rem;background:#f8fafc;">
                <button onclick="document.getElementById('complaintsModal').style.display='none'" style="background:#f1f5f9;border:1px solid #e2e8f0;color:#64748b;padding:0.55rem 1.4rem;border-radius:50px;font-size:0.82rem;font-weight:700;cursor:pointer;">Close</button>
                <a href="user/dashboard.php" style="background:#0f3057;color:#fff;text-decoration:none;padding:0.55rem 1.4rem;border-radius:50px;font-size:0.82rem;font-weight:700;">Go to Dashboard</a>
            </div>
        </div>
    </div>
    <!-- Close modal on backdrop click -->
    <script>
    document.getElementById('complaintsModal').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
    </script>
    <?php endif; ?>



    <!-- 1. Submit Section containing the Interactive Grid Cards -->
    <section class="page-section" id="submit">


        <!-- Row 1: The Four Colorful Role Cards matching your requested design -->
        <div class="row justify-content-center g-4 mb-4">
            <!-- Student Card -->
            <div class="col-lg-3 col-md-6">
                <a href="login.php?role=student" class="role-selector-card role-bg-student">
                    <div class="role-avatar-circle">
                        <!-- Student SVG Avatar -->
                        <svg width="102" height="102" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="48" fill="#ffb74d"/>
                            <!-- Back Left Student -->
                            <path d="M15,80 C15,68 25,62 32,62 C34,62 36,63 38,64 C35,68 35,74 38,78" fill="#e53935"/>
                            <circle cx="28" cy="46" r="8" fill="#ffcc80"/>
                            <polygon points="28,34 38,39 28,44 18,39" fill="#37474f"/>
                            
                            <!-- Back Right Student -->
                            <path d="M85,80 C85,68 75,62 68,62 C66,62 64,63 62,64 C65,68 65,74 62,78" fill="#e53935"/>
                            <circle cx="72" cy="46" r="8" fill="#ffcc80"/>
                            <polygon points="72,34 82,39 72,44 62,39" fill="#37474f"/>

                            <!-- Front Center Student (Female) -->
                            <path d="M22,88 C22,72 35,66 50,66 C65,66 78,72 78,88 Z" fill="#008080"/>
                            <rect x="46" y="54" width="8" height="15" fill="#ffe082"/>
                            <circle cx="50" cy="44" r="14" fill="#ffe082"/>
                            <path d="M36,44 C36,32 64,32 64,44 C64,48 60,48 58,46 Q50,42 42,46 C40,48 36,48 36,44" fill="#263238"/>
                            <polygon points="50,22 72,30 50,38 28,30" fill="#37474f"/>
                            <polygon points="46,36 46,50 50,48" fill="#ffe082"/>
                            <rect x="70" y="30" width="2" height="12" fill="#ffb300"/>
                            <circle cx="71" cy="42" r="2" fill="#ffb300"/>
                        </svg>
                    </div>
                    <span class="role-pill-btn btn-pill-student">Student</span>
                </a>
            </div>

            <!-- Staff Card -->
            <div class="col-lg-3 col-md-6">
                <a href="login.php?role=staff" class="role-selector-card role-bg-staff">
                    <div class="role-avatar-circle">
                        <!-- Staff SVG Avatar -->
                        <svg width="102" height="102" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="48" fill="#4fc3f7"/>
                            <!-- Back Left Staff -->
                            <path d="M15,80 C15,66 28,62 35,62" fill="#37474f"/>
                            <circle cx="28" cy="45" r="8" fill="#ffe082"/>
                            <path d="M22,39 Q28,33 34,39" fill="#263238" stroke="#263238" stroke-width="2"/>
                            
                            <!-- Back Right Staff -->
                            <path d="M85,80 C85,66 72,62 65,62" fill="#e53935"/>
                            <circle cx="72" cy="45" r="8" fill="#ffe082"/>
                            <path d="M66,39 Q72,33 78,39" fill="#d81b60" stroke="#d81b60" stroke-width="2"/>

                            <!-- Front Center Staff (Suit & Tie) -->
                            <path d="M22,88 C22,70 34,64 50,64 C66,64 78,70 78,88 Z" fill="#2d3748"/>
                            <rect x="46" y="56" width="8" height="15" fill="#ffe082"/>
                            <circle cx="50" cy="44" r="13" fill="#ffe082"/>
                            <path d="M37,42 C37,30 63,30 63,42 Q50,34 37,42" fill="#1a202c"/>
                            <polygon points="42,64 58,64 50,72" fill="#ffffff"/>
                            <polygon points="48,70 52,70 54,88 46,88" fill="#00b0ff"/>
                        </svg>
                    </div>
                    <span class="role-pill-btn btn-pill-staff">Staff / Faculty</span>
                </a>
            </div>

            <!-- Parent Card -->
            <div class="col-lg-3 col-md-6">
                <a href="login.php?role=parent" class="role-selector-card role-bg-parent">
                    <div class="role-avatar-circle">
                        <!-- Parent SVG Avatar -->
                        <svg width="102" height="102" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="48" fill="#f472b6"/>
                            <!-- Father (Left) -->
                            <path d="M12,88 C12,72 24,66 38,66 C48,66 52,72 54,88 Z" fill="#0066cc"/>
                            <circle cx="32" cy="44" r="11" fill="#ffe082"/>
                            <path d="M22,42 C22,34 42,34 42,42" fill="#2d3748"/>

                            <!-- Mother (Right) -->
                            <path d="M46,88 C46,74 56,68 70,68 C84,68 88,74 88,88 Z" fill="#ff9900"/>
                            <circle cx="68" cy="46" r="11" fill="#ffe082"/>
                            <path d="M58,46 C58,34 78,34 78,46" fill="#800000"/>
                        </svg>
                    </div>
                    <span class="role-pill-btn btn-pill-parent">Parent</span>
                </a>
            </div>

            <!-- Admin Card -->
            <div class="col-lg-3 col-md-6">
                <a href="login.php?role=admin" class="role-selector-card role-bg-admin">
                    <div class="role-avatar-circle">
                        <!-- Admin SVG Avatar (Shield with user inside) -->
                        <svg width="102" height="102" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="48" fill="#c084fc"/>
                            <!-- Shield Outline -->
                            <path d="M50,18 L76,28 L76,54 C76,68 64,78 50,82 C36,78 24,68 24,54 L24,28 Z" fill="#581c87" />
                            <!-- Inner Shield -->
                            <path d="M50,25 L70,33 L70,54 C70,64 60,73 50,76 C40,73 30,64 30,54 L30,33 Z" fill="#ffffff" />
                            <!-- User symbol inside shield -->
                            <circle cx="50" cy="42" r="7" fill="#581c87"/>
                            <path d="M38,62 C38,54 43,50 50,50 C57,50 62,54 62,62 Z" fill="#581c87" />
                        </svg>
                    </div>
                    <span class="role-pill-btn btn-pill-admin">Admin</span>
                </a>
            </div>
        </div>


    </section>

    <!-- About NSTI Campus Section -->
    <section class="page-section" id="about-campus" style="padding: 4.5rem 0; background-color: #f8fafc;">
        <div class="container">
            <div class="custom-section-header-container" style="display: flex; justify-content: center; width: 100%; margin-bottom: 3.5rem;">
                <div class="custom-section-header" style="display: flex; align-items: center; gap: 1.25rem;">
                    <div class="header-left-bar" style="width: 6px; height: 75px; background: linear-gradient(to bottom, #2e7d32, #4caf50); border-radius: 3px;"></div>
                    <div class="header-content-wrapper" style="display: flex; flex-direction: column; align-items: flex-start;">
                        <h2 class="header-title-text" style="font-size: 2.2rem; font-weight: 800; color: #0f3057; line-height: 1.25; margin: 0; text-transform: none;">
                            <i class="fas fa-university" style="color: #2e7d32; margin-right: 12px; font-size: 0.9em; vertical-align: middle;"></i>About NSTI Ramanthapur
                        </h2>
                        <div class="header-bottom-line" style="width: 180px; height: 4px; background-color: #2e7d32; margin-top: 10px; border-radius: 2px;"></div>
                    </div>
                </div>
            </div>

            <div class="row align-items-center g-5 position-relative">
                <!-- Text Column -->
                <div class="col-lg-7 position-relative z-2">
                    <div class="campus-info-card p-4 p-md-5" style="background: #ffffff; border-radius: 28px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 20px 50px rgba(15, 48, 87, 0.08); position: relative; overflow: hidden;">
                        
                        <!-- Decorative top gradient line -->
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #2e7d32, #4caf50, #81c784);"></div>
                        
                        <h3 style="font-size: 1.7rem; font-weight: 800; color: #0f3057; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px; letter-spacing: -0.5px;">
                            <span style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9); color: #2e7d32; width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 10px rgba(46, 125, 50, 0.15);">
                                <i class="fas fa-graduation-cap"></i>
                            </span>
                            Center of Vocational Excellence
                        </h3>
                        <p style="font-size: 1.05rem; color: #475569; line-height: 1.8; text-align: justify; margin-bottom: 1.2rem;">
                            <strong>National Skill Training Institute (NSTI), Ramanthapur, Hyderabad</strong> is a premier vocational training institute under the <strong>Directorate General of Training (DGT)</strong>, Ministry of Skill Development and Entrepreneurship (MSDE), Government of India.
                        </p>
                        <p style="font-size: 1.05rem; color: #475569; line-height: 1.8; text-align: justify; margin-bottom: 2rem;">
                            With a sprawling campus, modern workshops, state-of-the-art labs, and expert faculty, NSTI Hyderabad ensures training alignment with global industrial standards.
                        </p>
                        
                        <!-- Stylish Feature Pills -->
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="feature-pill d-flex align-items-center gap-2 p-2 pe-3 rounded-pill" style="background: #f8fafc; border: 1px solid #e2e8f0; transition: all 0.3s ease;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #2e7d32; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-check" style="font-size: 0.85rem;"></i>
                                    </div>
                                    <span style="font-weight: 700; color: #1e293b; font-size: 0.85rem; letter-spacing: 0.3px;">Instructor Training (CITS)</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="feature-pill d-flex align-items-center gap-2 p-2 pe-3 rounded-pill" style="background: #f8fafc; border: 1px solid #e2e8f0; transition: all 0.3s ease;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #2e7d32; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-check" style="font-size: 0.85rem;"></i>
                                    </div>
                                    <span style="font-weight: 700; color: #1e293b; font-size: 0.85rem; letter-spacing: 0.3px;">Advanced Diploma</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="feature-pill d-flex align-items-center gap-2 p-2 pe-3 rounded-pill" style="background: #f8fafc; border: 1px solid #e2e8f0; transition: all 0.3s ease;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #2e7d32; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-check" style="font-size: 0.85rem;"></i>
                                    </div>
                                    <span style="font-weight: 700; color: #1e293b; font-size: 0.85rem; letter-spacing: 0.3px;">Industry Training</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="feature-pill d-flex align-items-center gap-2 p-2 pe-3 rounded-pill" style="background: #f8fafc; border: 1px solid #e2e8f0; transition: all 0.3s ease;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #2e7d32; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-check" style="font-size: 0.85rem;"></i>
                                    </div>
                                    <span style="font-weight: 700; color: #1e293b; font-size: 0.85rem; letter-spacing: 0.3px;">State-of-the-art Labs</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Column -->
                <div class="col-lg-5 position-relative">
                    <!-- Decorative back square -->
                    <div style="position: absolute; top: -20px; right: -20px; bottom: 20px; left: 20px; background: linear-gradient(135deg, #15803d, #22c55e); border-radius: 28px; z-index: 0; opacity: 0.15;"></div>
                    
                    <div class="campus-img-container" style="position: relative; border-radius: 28px; overflow: hidden; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2); border: 5px solid #ffffff; z-index: 1; transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        <img src="assets/images/nsti_entrance.png" alt="NSTI Ramanthapur Entrance" style="width: 100%; height: auto; display: block; object-fit: cover; transition: transform 0.6s ease;" />
                        <div class="img-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(15, 48, 87, 0.95) 0%, rgba(15, 48, 87, 0.8) 40%, transparent 100%); padding: 2.5rem 1.5rem 1.5rem 1.5rem; color: #ffffff;">
                            <span style="font-weight: 800; font-size: 1.35rem; display: block; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 4px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">NSTI Ramanthapur</span>
                            <span style="font-size: 0.9rem; font-weight: 600; color: #a7f3d0;"><i class="fas fa-map-marker-alt" style="margin-right: 6px;"></i> Hyderabad, Telangana</span>
                        </div>
                    </div>
                    <style>
                        .campus-img-container:hover {
                            transform: translateY(-10px) rotate(1deg);
                            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
                        }
                        .campus-img-container:hover img {
                            transform: scale(1.08);
                        }
                        .feature-pill:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 15px rgba(0,0,0,0.06);
                            background: #ffffff !important;
                            border-color: #2e7d32 !important;
                        }
                    </style>
                </div>
            </div>
        </div>
    </section>

    <!-- Steps To Lodge The Grievance Section -->
    <section class="page-section" id="steps">
        <div class="custom-section-header-container">
            <div class="custom-section-header">
                <div class="header-left-bar"></div>
                <div class="header-content-wrapper">
                    <h2 class="header-title-text">Steps To Lodge The Grievance</h2>
                    <div class="header-bottom-line"></div>
                </div>
            </div>
        </div>

        <div class="section-card" style="position: relative; padding: 4.5rem 2.5rem; overflow: hidden; background-color: #ffffff;">
            <div class="steps-timeline-container">
                <!-- Curved dotted line with dynamic color gradient on desktop (connecting step 1 to step 4) -->
                <svg class="steps-wave-path" viewBox="0 0 1200 200" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="waveGradient" x1="12.5%" y1="0%" x2="87.5%" y2="0%">
                            <stop offset="0%" stop-color="#9333ea" />
                            <stop offset="33%" stop-color="#ea580c" />
                            <stop offset="66%" stop-color="#0d9488" />
                            <stop offset="100%" stop-color="#db2777" />
                        </linearGradient>
                    </defs>
                    <path d="M 150,160 C 225,160 375,40 450,40 C 525,40 675,160 750,160 C 825,160 975,40 1050,40" fill="none" stroke="url(#waveGradient)" stroke-width="4" stroke-linecap="round" stroke-dasharray="10, 10" />
                </svg>

                <div class="steps-row">
                    <!-- Step 1 -->
                    <div class="step-item valley purple-theme">
                        <div class="step-card">
                            <p class="step-desc">Navigate to the registration button and create user login by filling in basic details.</p>
                        </div>
                        <div class="step-circle">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="step-number">Step 01</div>
                    </div>

                    <!-- Step 2 -->
                    <div class="step-item peak orange-theme">
                        <div class="step-circle">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="step-number">Step 02</div>
                        <div class="step-card">
                            <p class="step-desc">Login and proceed to register your grievance by selecting the relevant category of grievance and click on submit to file the grievance.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="step-item valley teal-theme">
                        <div class="step-card">
                            <p class="step-desc">Track the status of the grievance in the user dashboard.</p>
                        </div>
                        <div class="step-circle">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="step-number">Step 03</div>
                    </div>

                    <!-- Step 4 -->
                    <div class="step-item peak pink-theme">
                        <div class="step-circle">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="step-number">Step 04</div>
                        <div class="step-card">
                            <p class="step-desc">Appeal to the higher authority, if the resolution of the grievance is not satisfactory.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Project Team Members Section -->
    <section class="page-section" id="committee">
        <div class="custom-section-header-container">
            <div class="custom-section-header">
                <div class="header-left-bar"></div>
                <div class="header-content-wrapper">
                    <h2 class="header-title-text"><i class="fas fa-users" style="color: #2e7d32; margin-right: 12px; font-size: 0.9em; vertical-align: middle;"></i>Meet Our Team</h2>
                    <div class="header-bottom-line"></div>
                </div>
            </div>
        </div>

        <!-- Committee Member Cards Marquee (reused for developers) -->
        <div class="committee-marquee-container mb-4">
            <div class="committee-marquee-track">
                <!-- Group 1 -->
                <div class="committee-marquee-group">
                    <!-- Developer 1 -->
                    <div class="committee-card">
                        <div class="committee-avatar">
                            <img src="assets/images/team_leader.jpg" alt="Aakanksha Netam">
                        </div>
                        <h5 class="committee-name">Aakanksha Netam</h5>
                        <p class="committee-role">Team Leader <i class="fas fa-chevron-down toggle-icon"></i></p>
                        <p class="committee-designation">Created a whole documentation for this project &amp; UI/UX Designer.</p>
                    </div>
                    
                    <!-- Developer 2 -->
                    <div class="committee-card">
                        <div class="committee-avatar">
                            <img src="assets/images/project_manager.png" alt="VIVEKANAND">
                        </div>
                        <h5 class="committee-name">VIVEKANAND</h5>
                        <p class="committee-role">Project Manager <i class="fas fa-chevron-down toggle-icon"></i></p>
                        <p class="committee-designation">Full-Stack Developer & UI/UX Designer ensuring a seamless experience for the Online Grievance Redressal System.</p>
                    </div>

                    <!-- Developer 3 -->
                    <div class="committee-card">
                        <div class="committee-avatar">
                            <img src="assets/images/ui_designer.jpg" alt="V.Vaishnavi">
                        </div>
                        <h5 class="committee-name">V.Vaishnavi</h5>
                        <p class="committee-role">UI/UX Designer <i class="fas fa-chevron-down toggle-icon"></i></p>
                        <p class="committee-designation">Designing intuitive and user-centric interfaces to ensure a seamless navigation experience.</p>
                    </div>

                    <!-- Developer 4 -->
                    <div class="committee-card">
                        <div class="committee-avatar">
                            <img src="assets/images/deenu_ram.jpg" alt="DEENU RAM">
                        </div>
                        <h5 class="committee-name">DEENU RAM</h5>
                        <p class="committee-role">UI/UX Designer <i class="fas fa-chevron-down toggle-icon"></i></p>
                        <p class="committee-designation">Crafting high-fidelity UI designs and interactive mockups to optimize user satisfaction.</p>
                    </div>
                </div>

                <!-- Group 2 (duplicate for seamless loop) -->
                <div class="committee-marquee-group">
                    <!-- Developer 1 -->
                    <div class="committee-card">
                        <div class="committee-avatar">
                            <img src="assets/images/team_leader.jpg" alt="Aakanksha Netam">
                        </div>
                        <h5 class="committee-name">Aakanksha Netam</h5>
                        <p class="committee-role">Team Leader <i class="fas fa-chevron-down toggle-icon"></i></p>
                        <p class="committee-designation">Created a whole documentation for this project &amp; UI/UX Designer.</p>
                    </div>
                    
                    <!-- Developer 2 -->
                    <div class="committee-card">
                        <div class="committee-avatar">
                            <img src="assets/images/project_manager.png" alt="VIVEKANAND">
                        </div>
                        <h5 class="committee-name">VIVEKANAND</h5>
                        <p class="committee-role">Project Manager <i class="fas fa-chevron-down toggle-icon"></i></p>
                        <p class="committee-designation">Full-Stack Developer & UI/UX Designer ensuring a seamless experience for the Online Grievance Redressal System.</p>
                    </div>

                    <!-- Developer 3 -->
                    <div class="committee-card">
                        <div class="committee-avatar">
                            <img src="assets/images/ui_designer.jpg" alt="V.Vaishnavi">
                        </div>
                        <h5 class="committee-name">V.Vaishnavi</h5>
                        <p class="committee-role">UI/UX Designer <i class="fas fa-chevron-down toggle-icon"></i></p>
                        <p class="committee-designation">Designing intuitive and user-centric interfaces to ensure a seamless navigation experience.</p>
                    </div>

                    <!-- Developer 4 -->
                    <div class="committee-card">
                        <div class="committee-avatar">
                            <img src="assets/images/deenu_ram.jpg" alt="DEENU RAM">
                        </div>
                        <h5 class="committee-name">DEENU RAM</h5>
                        <p class="committee-role">UI/UX Designer <i class="fas fa-chevron-down toggle-icon"></i></p>
                        <p class="committee-designation">Crafting high-fidelity UI designs and interactive mockups to optimize user satisfaction.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer containing About, Committee, Contact, and Watermark -->
    <footer class="site-footer" id="footer-sections">

    <!-- Premium Rating Section (above footer columns) -->
    <div class="footer-rating-section" id="website-rating">
        <div class="footer-rating-inner">

            <!-- Heading -->
            <p class="footer-rating-heading">
                <i class="fas fa-star star-icon-glow"></i>
                Rate Our Website
            </p>
            <!-- Interactive Stars -->
            <div class="star-rating-group" id="starGroup" role="group" aria-label="Website Rating">
                <input type="radio" id="star5" name="site-rating" value="5">
                <label for="star5" title="Excellent!">&#9733;</label>
                <input type="radio" id="star4" name="site-rating" value="4">
                <label for="star4" title="Very Good">&#9733;</label>
                <input type="radio" id="star3" name="site-rating" value="3">
                <label for="star3" title="Good">&#9733;</label>
                <input type="radio" id="star2" name="site-rating" value="2">
                <label for="star2" title="Fair">&#9733;</label>
                <input type="radio" id="star1" name="site-rating" value="1">
                <label for="star1" title="Poor">&#9733;</label>
            </div>

            <!-- Bottom: Submit + Average -->
            <div class="rating-bottom-row">
                <button class="rating-submit-btn" id="submitRatingBtn" onclick="submitRating()">
                    <i class="fas fa-paper-plane me-1"></i> Submit Rating
                </button>
                <div class="rating-avg-display" id="ratingAvgDisplay">
                    <span class="rating-avg-score" id="avgScore">0.0</span>
                    <span class="rating-avg-stars" id="avgStars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                    <span class="rating-avg-count" id="totalCount">0 ratings</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer Links Grid matching the user's mockup layout (4 columns) -->
    <div class="footer-links-grid container">
        <div class="row g-4">
            <!-- Column 1: About NSTI R-Hyderabad -->
            <div class="col-md-6 col-lg-3" id="about">
                <h5 class="footer-grid-title text-uppercase mb-4 fw-bold text-white"><i class="fas fa-info-circle" style="color: #2e7d32; margin-right: 10px; font-size: 0.9em; vertical-align: middle;"></i>About System</h5>
                <p class="footer-grid-text mb-4" style="text-align: justify; line-height: 1.65; color: #cbd5e1;">
                    The Online Grievance Redressal System is a digital initiative at NSTI Ramanthapur, Hyderabad, dedicated to establishing a transparent and fair environment. Our platform ensures that every concern is registered, tracked, and resolved efficiently and confidentially.
                </p>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="col-md-6 col-lg-3">
                <h5 class="footer-grid-title text-uppercase mb-4 fw-bold text-white"><i class="fas fa-link" style="color: #2e7d32; margin-right: 10px; font-size: 0.9em; vertical-align: middle;"></i>Quick Links</h5>
                
                <!-- Sub-option Group 1: Portals -->
                <span class="d-block text-white fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.5px; opacity: 0.8; font-size: 0.8rem;">Portals</span>
                <ul class="list-unstyled footer-links-list ps-2 mb-3">
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-chevron-right" style="font-size: 0.75rem; color: #a855f7;"></i>
                        <a href="login.php?role=student" class="footer-link text-decoration-none" style="font-size: 0.9rem;">Student Portal</a>
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-chevron-right" style="font-size: 0.75rem; color: #a855f7;"></i>
                        <a href="login.php?role=staff" class="footer-link text-decoration-none" style="font-size: 0.9rem;">Staff / Faculty Portal</a>
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-chevron-right" style="font-size: 0.75rem; color: #a855f7;"></i>
                        <a href="login.php?role=parent" class="footer-link text-decoration-none" style="font-size: 0.9rem;">Parent Portal</a>
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-chevron-right" style="font-size: 0.75rem; color: #a855f7;"></i>
                        <a href="login.php?role=admin" class="footer-link text-decoration-none" style="font-size: 0.9rem;">Administrator Login</a>
                    </li>
                </ul>

                <!-- Sub-option Group 2: Services -->
                <span class="d-block text-white fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.5px; opacity: 0.8; font-size: 0.8rem;">Services</span>
                <ul class="list-unstyled footer-links-list ps-2">
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-chevron-right" style="font-size: 0.75rem; color: #a855f7;"></i>
                        <a href="check_status.php" class="footer-link text-decoration-none" style="font-size: 0.9rem;">Track Status</a>
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-chevron-right" style="font-size: 0.75rem; color: #a855f7;"></i>
                        <a href="forgot_password.php" class="footer-link text-decoration-none" style="font-size: 0.9rem;">Reset Password</a>
                    </li>
                </ul>
            </div>

            <!-- Column 3: Our Team -->
            <div class="col-md-6 col-lg-3">
                <h5 class="footer-grid-title text-uppercase mb-4 fw-bold text-white"><i class="fas fa-users" style="color: #2e7d32; margin-right: 10px; font-size: 0.9em; vertical-align: middle;"></i>Our Team</h5>
                
                <ul class="list-unstyled footer-links-list ps-2 text-secondary-white" style="font-size: 0.9rem; line-height: 1.8;">
                    <li class="mb-2.5 d-flex align-items-start gap-2">
                        <i class="fas fa-user-tie mt-1" style="color: #a855f7; font-size: 0.85rem;"></i>
                        <div>
                            <span class="d-block fw-bold text-white" style="font-size: 0.9rem;">Aakanksha Netam</span>
                            <span style="color: #cbd5e1; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; display: block; margin-top: 1px;">Team Leader</span>
                        </div>
                    </li>
                    <li class="mb-2.5 d-flex align-items-start gap-2">
                        <i class="fas fa-tasks mt-1" style="color: #a855f7; font-size: 0.85rem;"></i>
                        <div>
                            <span class="d-block fw-bold text-white" style="font-size: 0.9rem;">VIVEKANAND</span>
                            <span style="color: #cbd5e1; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; display: block; margin-top: 1px;">Project Manager</span>
                        </div>
                    </li>
                    <li class="mb-2.5 d-flex align-items-start gap-2">
                        <i class="fas fa-palette mt-1" style="color: #a855f7; font-size: 0.85rem;"></i>
                        <div>
                            <span class="d-block fw-bold text-white" style="font-size: 0.9rem;">V.Vaishnavi</span>
                            <span style="color: #cbd5e1; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; display: block; margin-top: 1px;">UI/UX Designer</span>
                        </div>
                    </li>
                    <li class="mb-2.5 d-flex align-items-start gap-2">
                        <i class="fas fa-palette mt-1" style="color: #a855f7; font-size: 0.85rem;"></i>
                        <div>
                            <span class="d-block fw-bold text-white" style="font-size: 0.9rem;">DEENU RAM</span>
                            <span style="color: #cbd5e1; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; display: block; margin-top: 1px;">UI/UX Designer</span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Column 4: Contact Us -->
            <div class="col-md-6 col-lg-3" id="contact">
                <h5 class="footer-grid-title text-uppercase mb-4 fw-bold text-white"><i class="fas fa-envelope-open-text" style="color: #2e7d32; margin-right: 10px; font-size: 0.9em; vertical-align: middle;"></i>Contact Us</h5>
                <ul class="list-unstyled footer-contact-list">
                    <li class="mb-3 text-secondary-white d-flex align-items-start gap-2">
                        <i class="fas fa-map-marker-alt mt-1"></i>
                        <span>Uppal Road, Ramanthapur, Rahat Nagar, Amberpet, Hyderabad, Telangana 500013</span>
                    </li>
                    <li class="mb-3 text-secondary-white d-flex align-items-center gap-2">
                        <i class="fas fa-phone-alt"></i>
                        <span>040-27037266, 040-27038982</span>
                    </li>
                    <li class="mb-3 text-secondary-white d-flex align-items-center gap-2">
                        <i class="fas fa-envelope"></i>
                        <span>nsti-hyderabad2@dgt.gov.in</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="rating-toast" id="ratingToast">
        <i class="fas fa-check-circle" style="color:#fbbf24; font-size:1.1rem;"></i>
        <span id="toastMessage">Rating submitted! Thank you &#128591;</span>
    </div>

    <!-- Footer Branding Watermark -->
                <div class="footer-watermark">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <p class="watermark-text mb-0 text-center text-md-start" style="font-size: 0.85rem;">&copy; <?php echo date('Y'); ?> Online Grievance Redressal System. All Rights Reserved.</p>
            <div class="d-flex justify-content-center justify-content-md-end align-items-center" style="font-size: 0.75rem; color: #64748b; padding: 5px 0;">
    <span style="opacity: 0.7; margin-right: 4px;">Developed By:</span>
    <span style="color: rgba(255,255,255,0.7); font-weight: 400;">Vivekanand & Aakanksha</span>
</div>
        </div>
    </div>
</footer>

    <!-- Smooth Active State Selection & Hero Slider Script -->
    <script>
        // Mobile Navigation Toggle
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById("mobile-menu-toggle");
            const navMenu = document.getElementById("navMenu");
            
            if (toggleBtn && navMenu) {
                toggleBtn.addEventListener("click", function() {
                    navMenu.classList.toggle("show");
                    const icon = toggleBtn.querySelector("i");
                    if (icon) {
                        if (navMenu.classList.contains("show")) {
                            icon.classList.remove("fa-bars");
                            icon.classList.add("fa-times");
                        } else {
                            icon.classList.remove("fa-times");
                            icon.classList.add("fa-bars");
                            
                            // Reset all dropdowns when menu is closed
                            const dropdownMenus = navMenu.querySelectorAll(".dropdown-menu");
                            dropdownMenus.forEach(menu => menu.classList.remove("open"));
                            const dropdownToggles = navMenu.querySelectorAll(".dropdown-toggle");
                            dropdownToggles.forEach(t => t.setAttribute("aria-expanded", "false"));
                        }
                    }
                });

                // Close menu when clicking a link (useful for hash links)
                const navLinks = document.querySelectorAll(".nav-menu a");
                navLinks.forEach(link => {
                    link.addEventListener("click", () => {
                        if (!link.classList.contains("dropdown-toggle")) {
                            navMenu.classList.remove("show");
                            const icon = toggleBtn.querySelector("i");
                            if (icon) {
                                icon.classList.remove("fa-times");
                                icon.classList.add("fa-bars");
                            }
                            // Reset all dropdowns
                            const dropdownMenus = navMenu.querySelectorAll(".dropdown-menu");
                            dropdownMenus.forEach(menu => menu.classList.remove("open"));
                            const dropdownToggles = navMenu.querySelectorAll(".dropdown-toggle");
                            dropdownToggles.forEach(t => t.setAttribute("aria-expanded", "false"));
                        }
                    });
                });

                // Dropdown toggle on mobile (prevents Bootstrap Popper conflicts)
                const dropdownToggles = document.querySelectorAll(".nav-menu .dropdown-toggle");
                dropdownToggles.forEach(toggle => {
                    toggle.addEventListener("click", function(e) {
                        const isMobile = window.getComputedStyle(toggleBtn).display !== "none";
                        if (isMobile) {
                            e.preventDefault();
                            e.stopPropagation();
                            const dropdownMenu = this.nextElementSibling;
                            if (dropdownMenu) {
                                const isShown = dropdownMenu.classList.toggle("open");
                                this.setAttribute("aria-expanded", isShown ? "true" : "false");
                            }
                        }
                    });
                });
            }
        });

        const sections = document.querySelectorAll("section, body");
        const navLi = document.querySelectorAll(".nav-menu a");
        window.addEventListener("scroll", () => {
            let current = "";
            sections.forEach((section) => {
                const sectionTop = section.offsetTop;
                if (window.pageYOffset >= sectionTop - 120) {
                    current = section.getAttribute("id");
                }
            });

            let dropdownActive = false;
            navLi.forEach((a) => {
                a.classList.remove("active");
                if (a.getAttribute("href") === `#${current}`) {
                    a.classList.add("active");
                    if (a.classList.contains("dropdown-item")) {
                        dropdownActive = true;
                    }
                }
            });

            const dropdownToggle = document.getElementById("grievanceDropdown");
            if (dropdownToggle) {
                if (dropdownActive) {
                    dropdownToggle.classList.add("active");
                } else {
                    dropdownToggle.classList.remove("active");
                }
            }
        });

        // Carousel Slider Functionality
        const slides = document.querySelectorAll(".hero-slide");
        const dots = document.querySelectorAll(".carousel-indicators-dots .dot");
        const prevBtn = document.getElementById("prev-slide");
        const nextBtn = document.getElementById("next-slide");
        let currentSlide = 0;
        let slideInterval;

        function showSlide(index) {
            if (slides.length === 0) return;
            slides.forEach(slide => slide.classList.remove("active"));
            dots.forEach(dot => dot.classList.remove("active"));
            
            slides[index].classList.add("active");
            dots[index].classList.add("active");
            currentSlide = index;
        }

        function nextSlide() {
            let next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }

        function prevSlide() {
            let prev = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(prev);
        }

        function startAutoPlay() {
            stopAutoPlay();
            slideInterval = setInterval(nextSlide, 5000);
        }

        function stopAutoPlay() {
            if (slideInterval) {
                clearInterval(slideInterval);
            }
        }

        if (prevBtn && nextBtn) {
            prevBtn.addEventListener("click", () => {
                prevSlide();
                startAutoPlay();
            });
            nextBtn.addEventListener("click", () => {
                nextSlide();
                startAutoPlay();
            });
        }

        dots.forEach(dot => {
            dot.addEventListener("click", (e) => {
                const targetSlide = parseInt(e.target.getAttribute("data-slide"));
                showSlide(targetSlide);
                startAutoPlay();
            });
        });

        // Initialize Slider
        if (slides.length > 0) {
            showSlide(0);
            startAutoPlay();
        }

        // Toggle team member card description on click
        document.querySelectorAll('.committee-card').forEach(card => {
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'button');
            card.setAttribute('aria-expanded', 'false');

            const toggleDesc = () => {
                const isExpanded = card.classList.toggle('show-description');
                card.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            };

            card.addEventListener('click', toggleDesc);
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleDesc();
                }
            });
        });

        // ===== Website Rating Widget Logic =====
        (function initRating() {
            const KEY_TOTAL   = 'nsti_rating_total';
            const KEY_COUNT   = 'nsti_rating_count';
            const KEY_VOTED   = 'nsti_rating_voted';

            const avgScore    = document.getElementById('avgScore');
            const avgStars    = document.getElementById('avgStars');
            const totalCount  = document.getElementById('totalCount');
            const submitBtn   = document.getElementById('submitRatingBtn');
            const starInputs  = document.querySelectorAll('#starGroup input[type="radio"]');

            // Render average on page load
            renderAvg();

            // If user already voted this session, disable stars
            if (localStorage.getItem(KEY_VOTED) === '1') {
                disableStars();
            }

            // Show submit button when a star is selected
            starInputs.forEach(input => {
                input.addEventListener('change', () => {
                    submitBtn.style.display = 'inline-block';
                });
            });

            function renderAvg() {
                const total = parseFloat(localStorage.getItem(KEY_TOTAL) || '0');
                const count = parseInt(localStorage.getItem(KEY_COUNT) || '0', 10);
                const avg   = count > 0 ? (total / count) : 0;

                avgScore.textContent = avg.toFixed(1);
                totalCount.textContent = count + (count === 1 ? ' rating' : ' ratings');

                // Render filled stars based on avg
                const filled = Math.round(avg);
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    starsHtml += i <= filled ? '&#9733;' : '&#9734;';
                }
                avgStars.innerHTML = starsHtml;
                avgStars.style.color = avg > 0 ? '#fbbf24' : 'rgba(255,255,255,0.3)';
            }

            function disableStars() {
                starInputs.forEach(input => input.disabled = true);
                document.querySelectorAll('#starGroup label').forEach(lbl => {
                    lbl.style.cursor = 'default';
                    lbl.style.opacity = '0.6';
                });
                submitBtn.style.display = 'none';
            }
        })();

        window.submitRating = function() {
            const KEY_TOTAL  = 'nsti_rating_total';
            const KEY_COUNT  = 'nsti_rating_count';
            const KEY_VOTED  = 'nsti_rating_voted';

            const selected = document.querySelector('#starGroup input[type="radio"]:checked');
            if (!selected) return;

            const val   = parseInt(selected.value, 10);
            const total = parseFloat(localStorage.getItem(KEY_TOTAL) || '0') + val;
            const count = parseInt(localStorage.getItem(KEY_COUNT) || '0', 10) + 1;

            localStorage.setItem(KEY_TOTAL, total);
            localStorage.setItem(KEY_COUNT, count);
            localStorage.setItem(KEY_VOTED, '1');

            // Update display
            const avg = total / count;
            document.getElementById('avgScore').textContent = avg.toFixed(1);
            document.getElementById('totalCount').textContent = count + (count === 1 ? ' rating' : ' ratings');
            const filled = Math.round(avg);
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) starsHtml += i <= filled ? '&#9733;' : '&#9734;';
            document.getElementById('avgStars').innerHTML = starsHtml;

            // Disable stars after submission
            document.querySelectorAll('#starGroup input[type="radio"]').forEach(i => i.disabled = true);
            document.querySelectorAll('#starGroup label').forEach(l => { l.style.cursor = 'default'; l.style.opacity = '0.6'; });
            document.getElementById('submitRatingBtn').style.display = 'none';

            // Show toast
            const labels = ['', 'Poor ðŸ˜ž', 'Fair ðŸ™‚', 'Good ðŸ‘', 'Very Good ðŸ˜Š', 'Excellent! ðŸ¤©'];
            const toast = document.getElementById('ratingToast');
            document.getElementById('toastMessage').innerHTML = `<strong>${labels[val]}</strong> â€” Thank you for your feedback! ðŸ™`;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom AI Chatbot UI -->
<link rel="stylesheet" href="assets/css/chatbot.css">
<div class="chatbot-container" id="chatbotContainer">
    <div class="chatbot-window">
        <div class="chatbot-header">
            <div class="chatbot-header-icon">
                <i class="fas fa-robot"></i>
            </div>
            <div class="chatbot-header-info">
                <h4>NSTI Copilot</h4>
                <p>AI Support Assistant</p>
            </div>
        </div>
        <div class="chatbot-body" id="chatBody">
            <div class="chat-msg bot">Hello! I am NSTI Copilot, the AI assistant for NSTI. How can I help you today?</div>
            <div class="chat-loading" id="chatLoading">
                <span></span><span></span><span></span>
            </div>
        </div>
        <div class="chatbot-input">
            <textarea id="chatInput" placeholder="Type your message..." onkeypress="handleChatKeypress(event)"></textarea>
            <button onclick="sendChatMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <div class="chatbot-toggler" onclick="toggleChatbot()">
        <i class="fas fa-comment-dots"></i>
        <i class="fas fa-times"></i>
    </div>
</div>
<script src="assets/js/chatbot.js"></script>

</body>
</html>





















