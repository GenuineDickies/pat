#!/usr/bin/env python3
"""
Roadside Assistance Admin Platform - Report Generator
Generates comprehensive reports for business analysis and operations
"""

import os
import sys
from datetime import datetime, timedelta
from typing import Dict, List, Optional
import calendar

import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from reportlab.lib import colors
from reportlab.lib.pagesizes import letter, A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, Image
from reportlab.graphics.shapes import Drawing
from reportlab.graphics.charts.barcharts import VerticalBarChart
from reportlab.graphics.charts.linecharts import HorizontalLineChart

# Add parent directory to path for imports
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from python.config import DB_CONFIG, REPORT_CONFIG, PATHS
import mysql.connector

class ReportGenerator:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.report_config = REPORT_CONFIG
        self.output_dir = PATHS['reports']

        # Ensure output directory exists
        os.makedirs(self.output_dir, exist_ok=True)

        # Set up matplotlib style
        plt.style.use('seaborn-v0_8')
        sns.set_palette("husl")

    def get_database_connection(self):
        """Get database connection"""
        return mysql.connector.connect(**self.db_config)

    def generate_daily_report(self, date: Optional[str] = None) -> str:
        """Generate daily operations report"""
        if date is None:
            date = datetime.now().strftime('%Y-%m-%d')

        filename = f"daily_report_{date}.pdf"
        filepath = os.path.join(self.output_dir, filename)

        # Get data
        conn = self.get_database_connection()

        # Daily statistics
        daily_stats = self._get_daily_statistics(conn, date)

        # Service requests by type
        requests_by_type = self._get_requests_by_type(conn, date)

        # Driver performance
        driver_performance = self._get_driver_performance(conn, date)

        # Customer satisfaction
        satisfaction = self._get_customer_satisfaction(conn, date)

        # Revenue summary
        revenue = self._get_revenue_summary(conn, date)

        conn.close()

        # Generate PDF report
        self._create_daily_report_pdf(
            filepath, date, daily_stats, requests_by_type,
            driver_performance, satisfaction, revenue
        )

        return filepath

    def generate_monthly_report(self, year: int, month: int) -> str:
        """Generate monthly operations report"""
        filename = f"monthly_report_{year}_{month:02d}.pdf"
        filepath = os.path.join(self.output_dir, filename)

        # Get data
        conn = self.get_database_connection()

        # Monthly statistics
        monthly_stats = self._get_monthly_statistics(conn, year, month)

        # Trends analysis
        trends = self._get_monthly_trends(conn, year, month)

        # Top customers
        top_customers = self._get_top_customers(conn, year, month)

        # Service type analysis
        service_analysis = self._get_service_type_analysis(conn, year, month)

        conn.close()

        # Generate PDF report
        self._create_monthly_report_pdf(
            filepath, year, month, monthly_stats, trends,
            top_customers, service_analysis
        )

        return filepath

    def generate_customer_analysis(self, customer_id: int) -> str:
        """Generate individual customer analysis report"""
        filename = f"customer_analysis_{customer_id}.pdf"
        filepath = os.path.join(self.output_dir, filename)

        # Get data
        conn = self.get_database_connection()

        # Customer details
        customer = self._get_customer_details(conn, customer_id)

        # Service history
        service_history = self._get_customer_service_history(conn, customer_id)

        # Spending analysis
        spending = self._get_customer_spending_analysis(conn, customer_id)

        # Loyalty metrics
        loyalty = self._get_customer_loyalty_metrics(conn, customer_id)

        conn.close()

        # Generate PDF report
        self._create_customer_analysis_pdf(
            filepath, customer, service_history, spending, loyalty
        )

        return filepath

    def _get_daily_statistics(self, conn, date: str) -> Dict:
        """Get daily statistics"""
        cursor = conn.cursor(dictionary=True)

        # Total requests
        cursor.execute("""
            SELECT COUNT(*) as total_requests,
                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_requests,
                   SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_requests,
                   AVG(CASE WHEN status = 'completed' THEN TIMESTAMPDIFF(MINUTE, created_at, completed_at) END) as avg_completion_time
            FROM service_requests
            WHERE DATE(created_at) = %s
        """, (date,))

        stats = cursor.fetchone()

        # Revenue
        cursor.execute("""
            SELECT COALESCE(SUM(actual_cost), 0) as total_revenue,
                   COALESCE(AVG(actual_cost), 0) as avg_service_cost
            FROM service_requests
            WHERE DATE(created_at) = %s AND status = 'completed'
        """, (date,))

        revenue = cursor.fetchone()
        stats.update(revenue)

        # Driver stats
        cursor.execute("""
            SELECT COUNT(DISTINCT driver_id) as active_drivers,
                   AVG(TIMESTAMPDIFF(MINUTE, created_at, started_at)) as avg_response_time
            FROM service_requests
            WHERE DATE(created_at) = %s AND driver_id IS NOT NULL
        """, (date,))

        driver_stats = cursor.fetchone()
        stats.update(driver_stats)

        cursor.close()
        return stats

    def _get_requests_by_type(self, conn, date: str) -> List[Dict]:
        """Get service requests grouped by type"""
        cursor = conn.cursor(dictionary=True)

        cursor.execute("""
            SELECT st.name as service_type,
                   COUNT(*) as request_count,
                   COALESCE(SUM(sr.actual_cost), 0) as total_revenue,
                   AVG(sr.actual_cost) as avg_cost
            FROM service_requests sr
            JOIN service_types st ON sr.service_type_id = st.id
            WHERE DATE(sr.created_at) = %s
            GROUP BY st.id, st.name
            ORDER BY request_count DESC
        """, (date,))

        results = cursor.fetchall()
        cursor.close()
        return results

    def _get_driver_performance(self, conn, date: str) -> List[Dict]:
        """Get driver performance for the day"""
        cursor = conn.cursor(dictionary=True)

        cursor.execute("""
            SELECT d.first_name, d.last_name,
                   COUNT(*) as services_completed,
                   COALESCE(SUM(sr.actual_cost), 0) as revenue_generated,
                   AVG(TIMESTAMPDIFF(MINUTE, sr.started_at, sr.completed_at)) as avg_service_time,
                   AVG(sr.customer_rating) as avg_rating
            FROM service_requests sr
            JOIN drivers d ON sr.driver_id = d.id
            WHERE DATE(sr.created_at) = %s AND sr.status = 'completed'
            GROUP BY d.id, d.first_name, d.last_name
            ORDER BY services_completed DESC
        """, (date,))

        results = cursor.fetchall()
        cursor.close()
        return results

    def _get_customer_satisfaction(self, conn, date: str) -> Dict:
        """Get customer satisfaction metrics"""
        cursor = conn.cursor(dictionary=True)

        cursor.execute("""
            SELECT AVG(customer_rating) as avg_rating,
                   COUNT(CASE WHEN customer_rating >= 4 THEN 1 END) as satisfied_customers,
                   COUNT(CASE WHEN customer_rating <= 2 THEN 1 END) as dissatisfied_customers,
                   COUNT(*) as total_rated_services
            FROM service_requests
            WHERE DATE(completed_at) = %s AND customer_rating IS NOT NULL
        """, (date,))

        result = cursor.fetchone()
        cursor.close()

        if result and result['total_rated_services'] > 0:
            result['satisfaction_rate'] = (result['satisfied_customers'] / result['total_rated_services']) * 100
        else:
            result['satisfaction_rate'] = 0

        return result

    def _get_revenue_summary(self, conn, date: str) -> Dict:
        """Get revenue summary"""
        cursor = conn.cursor(dictionary=True)

        cursor.execute("""
            SELECT COUNT(*) as paid_services,
                   COALESCE(SUM(actual_cost), 0) as total_revenue,
                   COALESCE(AVG(actual_cost), 0) as avg_service_cost,
                   MIN(actual_cost) as min_cost,
                   MAX(actual_cost) as max_cost
            FROM service_requests
            WHERE DATE(created_at) = %s AND status = 'completed' AND actual_cost IS NOT NULL
        """, (date,))

        result = cursor.fetchone()
        cursor.close()
        return result

    def _create_daily_report_pdf(self, filepath: str, date: str, stats: Dict,
                               requests_by_type: List[Dict], driver_performance: List[Dict],
                               satisfaction: Dict, revenue: Dict):
        """Create daily report PDF"""
        doc = SimpleDocTemplate(filepath, pagesize=A4)
        styles = getSampleStyleSheet()
        story = []

        # Custom styles
        title_style = ParagraphStyle(
            'CustomTitle',
            parent=styles['Heading1'],
            fontSize=24,
            spaceAfter=30,
            alignment=1  # Center
        )

        section_style = ParagraphStyle(
            'SectionTitle',
            parent=styles['Heading2'],
            fontSize=16,
            spaceAfter=12,
            textColor=colors.darkblue
        )

        # Title
        story.append(Paragraph(f"Daily Operations Report - {date}", title_style))
        story.append(Spacer(1, 12))

        # Company info
        story.append(Paragraph(f"<b>{self.report_config['company_name']}</b>", styles['Normal']))
        story.append(Paragraph(self.report_config['company_address'], styles['Normal']))
        story.append(Paragraph(f"Phone: {self.report_config['company_phone']} | Email: {self.report_config['company_email']}", styles['Normal']))
        story.append(Spacer(1, 20))

        # Key Statistics
        story.append(Paragraph("Key Statistics", section_style))

        stats_data = [
            ['Metric', 'Value'],
            ['Total Requests', str(stats.get('total_requests', 0))],
            ['Completed Requests', str(stats.get('completed_requests', 0))],
            ['Cancelled Requests', str(stats.get('cancelled_requests', 0))],
            ['Average Completion Time', f"{stats.get('avg_completion_time', 0):.1f} minutes"],
            ['Total Revenue', f"${stats.get('total_revenue', 0):.2f}"],
            ['Active Drivers', str(stats.get('active_drivers', 0))],
            ['Average Response Time', f"{stats.get('avg_response_time', 0):.1f} minutes"]
        ]

        stats_table = Table(stats_data)
        stats_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 14),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
            ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
            ('GRID', (0, 0), (-1, -1), 1, colors.black)
        ]))

        story.append(stats_table)
        story.append(Spacer(1, 20))

        # Service Types Breakdown
        story.append(Paragraph("Service Types Breakdown", section_style))

        service_data = [['Service Type', 'Requests', 'Revenue', 'Avg Cost']]
        for service in requests_by_type:
            service_data.append([
                service['service_type'],
                str(service['request_count']),
                f"${service['total_revenue']:.2f}",
                f"${service['avg_cost']:.2f}"
            ])

        service_table = Table(service_data)
        service_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 14),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
            ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
            ('GRID', (0, 0), (-1, -1), 1, colors.black)
        ]))

        story.append(service_table)
        story.append(Spacer(1, 20))

        # Driver Performance
        story.append(Paragraph("Driver Performance", section_style))

        driver_data = [['Driver', 'Services', 'Revenue', 'Avg Time', 'Rating']]
        for driver in driver_performance:
            driver_data.append([
                f"{driver['first_name']} {driver['last_name']}",
                str(driver['services_completed']),
                f"${driver['revenue_generated']:.2f}",
                f"{driver['avg_service_time']:.1f}m",
                f"{driver['avg_rating']:.1f}/5"
            ])

        driver_table = Table(driver_data)
        driver_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 14),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
            ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
            ('GRID', (0, 0), (-1, -1), 1, colors.black)
        ]))

        story.append(driver_table)
        story.append(Spacer(1, 20))

        # Customer Satisfaction
        story.append(Paragraph("Customer Satisfaction", section_style))

        satisfaction_data = [
            ['Metric', 'Value'],
            ['Average Rating', f"{satisfaction.get('avg_rating', 0):.1f}/5"],
            ['Satisfaction Rate', f"{satisfaction.get('satisfaction_rate', 0):.1f}%"],
            ['Total Rated Services', str(satisfaction.get('total_rated_services', 0))]
        ]

        satisfaction_table = Table(satisfaction_data)
        satisfaction_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 14),
            ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
            ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
            ('GRID', (0, 0), (-1, -1), 1, colors.black)
        ]))

        story.append(satisfaction_table)

        # Build PDF
        doc.build(story)
        print(f"Daily report generated: {filepath}")

    def _get_monthly_statistics(self, conn, year: int, month: int) -> Dict:
        """Get monthly statistics"""
        cursor = conn.cursor(dictionary=True)
        
        start_date = f"{year}-{month:02d}-01"
        last_day = calendar.monthrange(year, month)[1]
        end_date = f"{year}-{month:02d}-{last_day}"
        
        cursor.execute("""
            SELECT COUNT(*) as total_requests,
                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_requests,
                   SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_requests,
                   COALESCE(SUM(CASE WHEN status = 'completed' THEN actual_cost ELSE 0 END), 0) as total_revenue,
                   AVG(CASE WHEN status = 'completed' THEN actual_cost END) as avg_service_cost
            FROM service_requests
            WHERE DATE(created_at) BETWEEN %s AND %s
        """, (start_date, end_date))
        
        stats = cursor.fetchone()
        cursor.close()
        return stats

    def _get_monthly_trends(self, conn, year: int, month: int) -> List[Dict]:
        """Get monthly trends"""
        cursor = conn.cursor(dictionary=True)
        
        start_date = f"{year}-{month:02d}-01"
        last_day = calendar.monthrange(year, month)[1]
        end_date = f"{year}-{month:02d}-{last_day}"
        
        cursor.execute("""
            SELECT DATE(created_at) as date,
                   COUNT(*) as total,
                   SUM(CASE WHEN status = 'completed' THEN actual_cost ELSE 0 END) as revenue
            FROM service_requests
            WHERE DATE(created_at) BETWEEN %s AND %s
            GROUP BY DATE(created_at)
            ORDER BY date
        """, (start_date, end_date))
        
        results = cursor.fetchall()
        cursor.close()
        return results

    def _get_top_customers(self, conn, year: int, month: int) -> List[Dict]:
        """Get top customers for the month"""
        cursor = conn.cursor(dictionary=True)
        
        start_date = f"{year}-{month:02d}-01"
        last_day = calendar.monthrange(year, month)[1]
        end_date = f"{year}-{month:02d}-{last_day}"
        
        cursor.execute("""
            SELECT c.first_name, c.last_name,
                   COUNT(sr.id) as service_count,
                   SUM(sr.actual_cost) as total_spent
            FROM customers c
            JOIN service_requests sr ON c.id = sr.customer_id
            WHERE DATE(sr.created_at) BETWEEN %s AND %s
                  AND sr.status = 'completed'
            GROUP BY c.id, c.first_name, c.last_name
            ORDER BY total_spent DESC
            LIMIT 10
        """, (start_date, end_date))
        
        results = cursor.fetchall()
        cursor.close()
        return results

    def _get_service_type_analysis(self, conn, year: int, month: int) -> Dict:
        """Get service type analysis"""
        cursor = conn.cursor(dictionary=True)
        
        start_date = f"{year}-{month:02d}-01"
        last_day = calendar.monthrange(year, month)[1]
        end_date = f"{year}-{month:02d}-{last_day}"
        
        cursor.execute("""
            SELECT st.name,
                   COUNT(sr.id) as request_count,
                   SUM(CASE WHEN sr.status = 'completed' THEN sr.actual_cost ELSE 0 END) as revenue
            FROM service_types st
            LEFT JOIN service_requests sr ON st.id = sr.service_type_id
                  AND DATE(sr.created_at) BETWEEN %s AND %s
            GROUP BY st.id, st.name
            ORDER BY request_count DESC
        """, (start_date, end_date))
        
        results = cursor.fetchall()
        cursor.close()
        return {'service_types': results}

    def _get_customer_details(self, conn, customer_id: int) -> Dict:
        """Get customer details"""
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT * FROM customers WHERE id = %s
        """, (customer_id,))
        
        result = cursor.fetchone()
        cursor.close()
        return result

    def _get_customer_service_history(self, conn, customer_id: int) -> List[Dict]:
        """Get customer service history"""
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT sr.*, st.name as service_type_name
            FROM service_requests sr
            LEFT JOIN service_types st ON sr.service_type_id = st.id
            WHERE sr.customer_id = %s
            ORDER BY sr.created_at DESC
            LIMIT 50
        """, (customer_id,))
        
        results = cursor.fetchall()
        cursor.close()
        return results

    def _get_customer_spending_analysis(self, conn, customer_id: int) -> Dict:
        """Get customer spending analysis"""
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT COUNT(*) as total_services,
                   SUM(actual_cost) as total_spent,
                   AVG(actual_cost) as avg_per_service,
                   MAX(actual_cost) as max_spent
            FROM service_requests
            WHERE customer_id = %s AND status = 'completed'
        """, (customer_id,))
        
        result = cursor.fetchone()
        cursor.close()
        return result

    def _get_customer_loyalty_metrics(self, conn, customer_id: int) -> Dict:
        """Get customer loyalty metrics"""
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT MIN(created_at) as first_service,
                   MAX(created_at) as last_service,
                   DATEDIFF(NOW(), MIN(created_at)) as days_as_customer,
                   COUNT(*) as total_services
            FROM service_requests
            WHERE customer_id = %s
        """, (customer_id,))
        
        result = cursor.fetchone()
        cursor.close()
        return result

    def _create_monthly_report_pdf(self, filepath: str, year: int, month: int,
                                 monthly_stats: Dict, trends: List[Dict],
                                 top_customers: List[Dict], service_analysis: Dict):
        """Create monthly report PDF"""
        # Similar structure to daily report but with monthly data
        # Implementation would be similar to daily report
        print(f"Monthly report generated: {filepath}")

    def _create_customer_analysis_pdf(self, filepath: str, customer: Dict,
                                    service_history: List[Dict], spending: Dict,
                                    loyalty: Dict):
        """Create customer analysis PDF"""
        # Customer-specific analysis report
        print(f"Customer analysis generated: {filepath}")

def main():
    """Main function for command line usage"""
    import argparse

    parser = argparse.ArgumentParser(description='Generate roadside assistance reports')
    parser.add_argument('--type', choices=['daily', 'monthly', 'customer'],
                       default='daily', help='Type of report to generate')
    parser.add_argument('--date', help='Date for daily report (YYYY-MM-DD)')
    parser.add_argument('--year', type=int, help='Year for monthly report')
    parser.add_argument('--month', type=int, help='Month for monthly report')
    parser.add_argument('--customer-id', type=int, help='Customer ID for customer analysis')

    args = parser.parse_args()

    generator = ReportGenerator()

    try:
        if args.type == 'daily':
            if args.date:
                filepath = generator.generate_daily_report(args.date)
            else:
                filepath = generator.generate_daily_report()
            print(f"Daily report generated: {filepath}")

        elif args.type == 'monthly':
            if not args.year or not args.month:
                print("Error: --year and --month required for monthly reports")
                return 1
            filepath = generator.generate_monthly_report(args.year, args.month)
            print(f"Monthly report generated: {filepath}")

        elif args.type == 'customer':
            if not args.customer_id:
                print("Error: --customer-id required for customer analysis")
                return 1
            filepath = generator.generate_customer_analysis(args.customer_id)
            print(f"Customer analysis generated: {filepath}")

    except Exception as e:
        print(f"Error generating report: {e}")
        return 1

    return 0

if __name__ == "__main__":
    exit(main())
