<?php
/**
 * Employee Skill Tracker - Landing Page
 */
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillTrack Pro - Employee Skill Tracker</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-layer-group"></i>
            Employee Skill Tracker
        </a>
        <ul class="navbar-nav">
            <li><a href="#features" class="nav-link">Features</a></li>
            <li><a href="#how-it-works" class="nav-link">How It Works</a></li>
            <li><a href="auth/login.php?role=admin" class="nav-link"><i class="fas fa-user-shield"></i> Admin</a></li>
            <li><a href="auth/login.php?role=hr" class="nav-link"><i class="fas fa-user-tie"></i> HR</a></li>
            <li><a href="auth/login.php?role=candidate" class="nav-link"><i class="fas fa-user"></i> Employee</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="landing-hero">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-check-circle"></i>
                Smart Employee Management System
            </div>
            
            <h1 class="hero-title">
                Track Skills. <span>Assign Projects.</span><br>
                Build Teams.
            </h1>
            
            <p class="hero-subtitle">
                A comprehensive platform for managing employee skills, conducting assessments,<br>
                and intelligently matching candidates to projects based on their expertise.
            </p>
            
            <div class="hero-buttons">
                <a href="auth/register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i>
                    Get Started
                </a>
                <a href="auth/login.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </a>
            </div>
            
            <div class="hero-stats">
                <div class="hero-stat">
                    <h4>10K+</h4>
                    <p>Employees Tracked</p>
                </div>
                <div class="hero-stat">
                    <h4>500+</h4>
                    <p>Projects Managed</p>
                </div>
                <div class="hero-stat">
                    <h4>98%</h4>
                    <p>Match Accuracy</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="main-content-full" style="background: rgba(15, 23, 42, 0.5);">
        <div style="text-align: center; margin-bottom: 50px;">
            <h2 style="font-size: 36px; margin-bottom: 16px;">Powerful Features</h2>
            <p style="color: var(--text-muted); font-size: 18px;">Everything you need to manage your workforce effectively</p>
        </div>
        
        <div class="dashboard-grid">
            <div class="glass-card" style="padding: 40px; text-align: center;">
                <div class="stat-icon primary" style="margin: 0 auto 20px; width: 80px; height: 80px; font-size: 32px;">
                    <i class="fas fa-brain"></i>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 12px;">Skill Management</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">Employees can add, update, and showcase their skills with proficiency levels and years of experience.</p>
            </div>
            
            <div class="glass-card" style="padding: 40px; text-align: center;">
                <div class="stat-icon success" style="margin: 0 auto 20px; width: 80px; height: 80px; font-size: 32px;">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 12px;">Online Assessments</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">Create timed MCQ assessments with automatic scoring and comprehensive result tracking.</p>
            </div>
            
            <div class="glass-card" style="padding: 40px; text-align: center;">
                <div class="stat-icon warning" style="margin: 0 auto 20px; width: 80px; height: 80px; font-size: 32px;">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 12px;">Project Management</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">Create projects, define required skills, and manage positions with an intuitive interface.</p>
            </div>
            
            <div class="glass-card" style="padding: 40px; text-align: center;">
                <div class="stat-icon danger" style="margin: 0 auto 20px; width: 80px; height: 80px; font-size: 32px;">
                    <i class="fas fa-percentage"></i>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 12px;">Smart Matching</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">AI-powered skill matching algorithm calculates compatibility percentages between employees and projects.</p>
            </div>
            
            <div class="glass-card" style="padding: 40px; text-align: center;">
                <div class="stat-icon primary" style="margin: 0 auto 20px; width: 80px; height: 80px; font-size: 32px;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 12px;">Analytics Dashboard</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">Visualize data with interactive charts showing employee stats, assessments, and project assignments.</p>
            </div>
            
            <div class="glass-card" style="padding: 40px; text-align: center;">
                <div class="stat-icon success" style="margin: 0 auto 20px; width: 80px; height: 80px; font-size: 32px;">
                    <i class="fas fa-file-export"></i>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 12px;">Reports & Export</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">Generate detailed reports on skills, assessments, and assignments with PDF and CSV export options.</p>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="main-content-full">
        <div style="text-align: center; margin-bottom: 50px;">
            <h2 style="font-size: 36px; margin-bottom: 16px;">How It Works</h2>
            <p style="color: var(--text-muted); font-size: 18px;">Simple steps to get started with SkillTrack Pro</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; max-width: 1000px; margin: 0 auto;">
            <div style="text-align: center; position: relative;">
                <div style="width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 700;">1</div>
                <h3 style="margin-bottom: 10px;">Register</h3>
                <p style="color: var(--text-muted); font-size: 14px;">Create an account as Admin, HR, or Employee with your details.</p>
            </div>
            
            <div style="text-align: center; position: relative;">
                <div style="width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 700;">2</div>
                <h3 style="margin-bottom: 10px;">Add Skills</h3>
                <p style="color: var(--text-muted); font-size: 14px;">Employees add their skills with proficiency levels and experience.</p>
            </div>
            
            <div style="text-align: center; position: relative;">
                <div style="width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 700;">3</div>
                <h3 style="margin-bottom: 10px;">Take Assessment</h3>
                <p style="color: var(--text-muted); font-size: 14px;">Complete timed assessments to validate your skills.</p>
            </div>
            
            <div style="text-align: center; position: relative;">
                <div style="width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: 700;">4</div>
                <h3 style="margin-bottom: 10px;">Get Assigned</h3>
                <p style="color: var(--text-muted); font-size: 14px;">HR matches you to projects based on skills and assessment scores.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section style="padding: 80px 30px; text-align: center;">
        <div class="glass-card" style="max-width: 800px; margin: 0 auto; padding: 60px;">
            <h2 style="font-size: 36px; margin-bottom: 20px;">Ready to Get Started?</h2>
            <p style="color: var(--text-muted); font-size: 18px; margin-bottom: 30px;">Join thousands of companies using SkillTrack Pro to manage their workforce efficiently.</p>
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="auth/register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </a>
                <a href="auth/login.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="padding: 40px 30px; border-top: 1px solid var(--border-glass); text-align: center;">
        <div style="margin-bottom: 20px;">
            <a href="index.php" style="font-size: 24px; font-weight: 700; color: var(--text-primary); text-decoration: none;">
                <i class="fas fa-layer-group" style="color: var(--primary-light);"></i> SkillTrack Pro
            </a>
        </div>
        <p style="color: var(--text-muted); font-size: 14px;">
            &copy; 2026 SkillTrack Pro. All rights reserved. | Employee Skill Tracker & Project Assignment System
        </p>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
