#!/usr/bin/env python3
"""
Roadside Assistance Admin Platform - Data Analyzer
Analyzes business data and provides insights for decision making
"""

import os
import sys
from datetime import datetime, timedelta
from typing import Dict, List, Tuple

import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.linear_model import LinearRegression
from sklearn.preprocessing import StandardScaler
from sklearn.cluster import KMeans

# Add parent directory to path for imports
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from python.config import DB_CONFIG, FEATURES
import mysql.connector

class DataAnalyzer:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.features = FEATURES

        # Set up matplotlib style
        plt.style.use('seaborn-v0_8')
        sns.set_palette("husl")

    def get_database_connection(self):
        """Get database connection"""
        return mysql.connector.connect(**self.db_config)

    def analyze_service_demand(self, days: int = 30) -> Dict:
        """Analyze service demand patterns"""
        conn = self.get_database_connection()

        # Get service requests data
        query = """
            SELECT DATE(created_at) as date,
                   HOUR(created_at) as hour,
                   service_type_id,
                   COUNT(*) as request_count,
                   AVG(TIMESTAMPDIFF(MINUTE, created_at, assigned_at)) as avg_response_time
            FROM service_requests
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %s DAY)
            GROUP BY DATE(created_at), HOUR(created_at), service_type_id
            ORDER BY date, hour
        """

        df = pd.read_sql(query, conn, params=[days])

        conn.close()

        if df.empty:
            return {'error': 'No data available for analysis'}

        # Analyze daily patterns
        daily_patterns = self._analyze_daily_patterns(df)

        # Analyze hourly patterns
        hourly_patterns = self._analyze_hourly_patterns(df)

        # Analyze service type distribution
        service_distribution = self._analyze_service_distribution(df)

        # Predict future demand
        predictions = self._predict_demand(df, days)

        return {
            'daily_patterns': daily_patterns,
            'hourly_patterns': hourly_patterns,
            'service_distribution': service_distribution,
            'predictions': predictions,
            'summary': self._generate_demand_summary(df)
        }

    def analyze_driver_performance(self, days: int = 30) -> Dict:
        """Analyze driver performance metrics"""
        conn = self.get_database_connection()

        query = """
            SELECT d.id, d.first_name, d.last_name,
                   COUNT(sr.id) as total_services,
                   SUM(sr.actual_cost) as total_revenue,
                   AVG(sr.actual_cost) as avg_service_cost,
                   AVG(TIMESTAMPDIFF(MINUTE, sr.created_at, sr.completed_at)) as avg_completion_time,
                   AVG(sr.customer_rating) as avg_rating,
                   COUNT(CASE WHEN sr.status = 'completed' THEN 1 END) as completed_services,
                   COUNT(CASE WHEN sr.status = 'cancelled' THEN 1 END) as cancelled_services
            FROM drivers d
            LEFT JOIN service_requests sr ON d.id = sr.driver_id
                   AND sr.created_at >= DATE_SUB(NOW(), INTERVAL %s DAY)
            GROUP BY d.id, d.first_name, d.last_name
            ORDER BY total_services DESC
        """

        df = pd.read_sql(query, conn, params=[days])
        conn.close()

        if df.empty:
            return {'error': 'No driver data available'}

        # Calculate performance scores
        df['completion_rate'] = df['completed_services'] / df['total_services'] * 100
        df['performance_score'] = self._calculate_performance_score(df)

        # Identify top performers
        top_performers = df.nlargest(5, 'performance_score')

        # Identify areas for improvement
        improvement_areas = self._identify_improvement_areas(df)

        return {
            'driver_metrics': df.to_dict('records'),
            'top_performers': top_performers.to_dict('records'),
            'improvement_areas': improvement_areas,
            'summary': self._generate_performance_summary(df)
        }

    def analyze_customer_behavior(self, days: int = 90) -> Dict:
        """Analyze customer behavior patterns"""
        conn = self.get_database_connection()

        query = """
            SELECT c.id, c.first_name, c.last_name, c.is_vip,
                   COUNT(sr.id) as total_services,
                   SUM(sr.actual_cost) as total_spent,
                   AVG(sr.actual_cost) as avg_service_cost,
                   MAX(sr.created_at) as last_service_date,
                   AVG(sr.customer_rating) as avg_rating_given,
                   DATEDIFF(NOW(), MIN(sr.created_at)) as days_since_first_service
            FROM customers c
            LEFT JOIN service_requests sr ON c.id = sr.customer_id
                   AND sr.created_at >= DATE_SUB(NOW(), INTERVAL %s DAY)
                   AND sr.status = 'completed'
            GROUP BY c.id, c.first_name, c.last_name, c.is_vip
            HAVING total_services > 0
        """

        df = pd.read_sql(query, conn, params=[days])
        conn.close()

        if df.empty:
            return {'error': 'No customer data available'}

        # Segment customers
        customer_segments = self._segment_customers(df)

        # Analyze VIP vs regular customers
        vip_analysis = self._analyze_vip_performance(df)

        # Predict customer lifetime value
        clv_predictions = self._predict_customer_lifetime_value(df)

        # Identify at-risk customers
        at_risk_customers = self._identify_at_risk_customers(df)

        return {
            'customer_segments': customer_segments,
            'vip_analysis': vip_analysis,
            'clv_predictions': clv_predictions,
            'at_risk_customers': at_risk_customers,
            'summary': self._generate_customer_summary(df)
        }

    def analyze_revenue_trends(self, days: int = 90) -> Dict:
        """Analyze revenue trends and patterns"""
        conn = self.get_database_connection()

        query = """
            SELECT DATE(sr.created_at) as date,
                   HOUR(sr.created_at) as hour,
                   st.name as service_type,
                   SUM(sr.actual_cost) as daily_revenue,
                   COUNT(*) as service_count,
                   AVG(sr.actual_cost) as avg_service_cost
            FROM service_requests sr
            JOIN service_types st ON sr.service_type_id = st.id
            WHERE sr.created_at >= DATE_SUB(NOW(), INTERVAL %s DAY)
                  AND sr.status = 'completed'
                  AND sr.actual_cost IS NOT NULL
            GROUP BY DATE(sr.created_at), HOUR(sr.created_at), st.name
            ORDER BY date, hour
        """

        df = pd.read_sql(query, conn, params=[days])
        conn.close()

        if df.empty:
            return {'error': 'No revenue data available'}

        # Calculate daily totals
        daily_totals = df.groupby('date')['daily_revenue'].sum().reset_index()

        # Calculate weekly averages
        weekly_averages = self._calculate_weekly_averages(daily_totals)

        # Analyze revenue by service type
        service_revenue = self._analyze_service_revenue(df)

        # Predict future revenue
        revenue_predictions = self._predict_revenue(daily_totals)

        # Identify peak revenue periods
        peak_periods = self._identify_peak_periods(df)

        return {
            'daily_totals': daily_totals.to_dict('records'),
            'weekly_averages': weekly_averages,
            'service_revenue': service_revenue,
            'revenue_predictions': revenue_predictions,
            'peak_periods': peak_periods,
            'summary': self._generate_revenue_summary(daily_totals, df)
        }

    def _analyze_daily_patterns(self, df: pd.DataFrame) -> Dict:
        """Analyze daily demand patterns"""
        daily_counts = df.groupby('date')['request_count'].sum()

        # Calculate statistics
        avg_daily = daily_counts.mean()
        max_day = daily_counts.idxmax()
        min_day = daily_counts.idxmin()
        std_daily = daily_counts.std()

        # Identify busiest and slowest days
        day_of_week = pd.to_datetime(daily_counts.index).day_name()
        weekday_avg = daily_counts.groupby(day_of_week).mean()

        return {
            'average_daily_requests': float(avg_daily),
            'busiest_day': str(max_day),
            'slowest_day': str(min_day),
            'daily_volatility': float(std_daily),
            'weekday_averages': weekday_avg.to_dict(),
            'trend': 'increasing' if daily_counts.iloc[-1] > daily_counts.iloc[0] else 'decreasing'
        }

    def _analyze_hourly_patterns(self, df: pd.DataFrame) -> Dict:
        """Analyze hourly demand patterns"""
        hourly_counts = df.groupby('hour')['request_count'].sum()

        # Find peak hours
        peak_hours = hourly_counts.nlargest(3).index.tolist()
        off_peak_hours = hourly_counts.nsmallest(3).index.tolist()

        # Calculate hourly distribution
        hourly_distribution = (hourly_counts / hourly_counts.sum() * 100).round(2)

        return {
            'peak_hours': peak_hours,
            'off_peak_hours': off_peak_hours,
            'hourly_distribution': hourly_distribution.to_dict(),
            'busiest_hour': int(hourly_counts.idxmax()),
            'slowest_hour': int(hourly_counts.idxmin())
        }

    def _analyze_service_distribution(self, df: pd.DataFrame) -> Dict:
        """Analyze service type distribution"""
        service_counts = df.groupby('service_type_id')['request_count'].sum()

        # Calculate percentages
        total_requests = service_counts.sum()
        service_percentages = (service_counts / total_requests * 100).round(2)

        # Most and least popular services
        most_popular = service_counts.idxmax()
        least_popular = service_counts.idxmin()

        return {
            'service_counts': service_counts.to_dict(),
            'service_percentages': service_percentages.to_dict(),
            'most_popular_service': int(most_popular),
            'least_popular_service': int(least_popular),
            'total_unique_services': len(service_counts)
        }

    def _predict_demand(self, df: pd.DataFrame, forecast_days: int) -> Dict:
        """Predict future demand using linear regression"""
        if not self.features.get('advanced_analytics', False):
            return {'error': 'Advanced analytics not enabled'}

        try:
            # Prepare data for prediction
            daily_demand = df.groupby('date')['request_count'].sum().reset_index()
            daily_demand['date'] = pd.to_datetime(daily_demand['date'])
            daily_demand['day_of_year'] = daily_demand['date'].dt.dayofyear

            # Train model
            X = daily_demand[['day_of_year']]
            y = daily_demand['request_count']

            model = LinearRegression()
            model.fit(X, y)

            # Predict future demand
            last_date = daily_demand['date'].max()
            future_dates = [last_date + timedelta(days=i) for i in range(1, forecast_days + 1)]

            future_predictions = []
            for date in future_dates:
                prediction = model.predict([[date.dayofyear]])[0]
                future_predictions.append({
                    'date': date.strftime('%Y-%m-%d'),
                    'predicted_demand': max(0, round(prediction, 1))
                })

            return {
                'model_accuracy': model.score(X, y),
                'predictions': future_predictions,
                'trend': 'increasing' if model.coef_[0] > 0 else 'decreasing'
            }

        except Exception as e:
            return {'error': f'Prediction failed: {str(e)}'}

    def _calculate_performance_score(self, df: pd.DataFrame) -> pd.Series:
        """Calculate overall performance score for drivers"""
        # Normalize metrics (0-1 scale)
        df_normalized = df.copy()

        # Completion rate (0-1)
        df_normalized['completion_rate'] = df_normalized['completion_rate'] / 100

        # Rating (0-1)
        df_normalized['rating'] = df_normalized['avg_rating'] / 5

        # Revenue (normalized)
        max_revenue = df_normalized['total_revenue'].max()
        df_normalized['revenue_score'] = df_normalized['total_revenue'] / max_revenue if max_revenue > 0 else 0

        # Completion time (inverse - lower is better)
        max_time = df_normalized['avg_completion_time'].max()
        df_normalized['time_score'] = 1 - (df_normalized['avg_completion_time'] / max_time) if max_time > 0 else 1

        # Calculate weighted score
        weights = {
            'completion_rate': 0.3,
            'rating': 0.3,
            'revenue_score': 0.2,
            'time_score': 0.2
        }

        df_normalized['performance_score'] = (
            df_normalized['completion_rate'] * weights['completion_rate'] +
            df_normalized['rating'] * weights['rating'] +
            df_normalized['revenue_score'] * weights['revenue_score'] +
            df_normalized['time_score'] * weights['time_score']
        ) * 100

        return df_normalized['performance_score']

    def _identify_improvement_areas(self, df: pd.DataFrame) -> List[str]:
        """Identify areas where drivers can improve"""
        areas = []

        # Low completion rate
        low_completion = df[df['completion_rate'] < 80]
        if not low_completion.empty:
            areas.append(f"{len(low_completion)} drivers have low completion rates")

        # Low ratings
        low_rating = df[df['avg_rating'] < 3.5]
        if not low_rating.empty:
            areas.append(f"{len(low_rating)} drivers have low customer ratings")

        # Long completion times
        slow_drivers = df[df['avg_completion_time'] > df['avg_completion_time'].quantile(0.75)]
        if not slow_drivers.empty:
            areas.append(f"{len(slow_drivers)} drivers have longer than average completion times")

        return areas

    def _segment_customers(self, df: pd.DataFrame) -> Dict:
        """Segment customers based on behavior"""
        # Create features for clustering
        features = df[['total_services', 'total_spent', 'avg_service_cost']].copy()

        # Handle missing values
        features = features.fillna(0)

        # Scale features
        scaler = StandardScaler()
        scaled_features = scaler.fit_transform(features)

        # Perform clustering
        kmeans = KMeans(n_clusters=4, random_state=42)
        df['segment'] = kmeans.fit_predict(scaled_features)

        # Analyze segments
        segments = {}
        for segment in df['segment'].unique():
            segment_data = df[df['segment'] == segment]
            segments[f'segment_{segment}'] = {
                'count': len(segment_data),
                'avg_services': float(segment_data['total_services'].mean()),
                'avg_spending': float(segment_data['total_spent'].mean()),
                'vip_percentage': float((segment_data['is_vip'].sum() / len(segment_data)) * 100)
            }

        return segments

    def _analyze_vip_performance(self, df: pd.DataFrame) -> Dict:
        """Compare VIP vs regular customer performance"""
        vip_customers = df[df['is_vip'] == True]
        regular_customers = df[df['is_vip'] == False]

        return {
            'vip_count': len(vip_customers),
            'regular_count': len(regular_customers),
            'vip_avg_services': float(vip_customers['total_services'].mean()),
            'regular_avg_services': float(regular_customers['total_services'].mean()),
            'vip_avg_spending': float(vip_customers['total_spent'].mean()),
            'regular_avg_spending': float(regular_customers['total_spent'].mean()),
            'vip_satisfaction': float(vip_customers['avg_rating_given'].mean()),
            'regular_satisfaction': float(regular_customers['avg_rating_given'].mean())
        }

    def _generate_demand_summary(self, df: pd.DataFrame) -> Dict:
        """Generate demand analysis summary"""
        total_requests = df['request_count'].sum()
        avg_daily = df.groupby('date')['request_count'].sum().mean()
        peak_hour = df.groupby('hour')['request_count'].sum().idxmax()

        return {
            'total_requests_analyzed': int(total_requests),
            'average_daily_requests': float(avg_daily),
            'busiest_hour': int(peak_hour),
            'analysis_period_days': len(df['date'].unique())
        }

    def _generate_performance_summary(self, df: pd.DataFrame) -> Dict:
        """Generate performance analysis summary"""
        return {
            'total_drivers': len(df),
            'average_completion_rate': float(df['completion_rate'].mean()),
            'average_rating': float(df['avg_rating'].mean()),
            'top_performer': df.iloc[0]['first_name'] + ' ' + df.iloc[0]['last_name'] if not df.empty else None,
            'improvement_needed': len(df[df['performance_score'] < 60])
        }

    def _generate_customer_summary(self, df: pd.DataFrame) -> Dict:
        """Generate customer analysis summary"""
        return {
            'total_customers': len(df),
            'vip_customers': int(df['is_vip'].sum()),
            'average_services_per_customer': float(df['total_services'].mean()),
            'average_spending_per_customer': float(df['total_spent'].mean()),
            'customer_satisfaction': float(df['avg_rating_given'].mean())
        }

    def _generate_revenue_summary(self, daily_totals: pd.DataFrame, df: pd.DataFrame) -> Dict:
        """Generate revenue analysis summary"""
        total_revenue = daily_totals['daily_revenue'].sum()
        avg_daily_revenue = daily_totals['daily_revenue'].mean()
        best_day = daily_totals.loc[daily_totals['daily_revenue'].idxmax()]

        return {
            'total_revenue': float(total_revenue),
            'average_daily_revenue': float(avg_daily_revenue),
            'best_day': best_day['date'].strftime('%Y-%m-%d'),
            'best_day_revenue': float(best_day['daily_revenue']),
            'analysis_period_days': len(daily_totals)
        }

    def _calculate_weekly_averages(self, daily_totals: pd.DataFrame) -> Dict:
        """Calculate weekly revenue averages"""
        daily_totals['week'] = pd.to_datetime(daily_totals['date']).dt.isocalendar().week
        weekly_avg = daily_totals.groupby('week')['daily_revenue'].mean()
        
        return {
            'weekly_averages': weekly_avg.to_dict(),
            'avg_weekly_revenue': float(weekly_avg.mean())
        }

    def _analyze_service_revenue(self, df: pd.DataFrame) -> Dict:
        """Analyze revenue by service type"""
        service_revenue = df.groupby('service_type').agg({
            'daily_revenue': 'sum',
            'service_count': 'sum',
            'avg_service_cost': 'mean'
        }).to_dict('index')
        
        return service_revenue

    def _predict_revenue(self, daily_totals: pd.DataFrame) -> Dict:
        """Predict future revenue using simple moving average"""
        if len(daily_totals) < 7:
            return {'error': 'Insufficient data for prediction'}
        
        # Simple 7-day moving average prediction
        recent_avg = daily_totals.tail(7)['daily_revenue'].mean()
        
        return {
            'predicted_daily_revenue': float(recent_avg),
            'predicted_weekly_revenue': float(recent_avg * 7),
            'predicted_monthly_revenue': float(recent_avg * 30),
            'confidence': 'medium'
        }

    def _identify_peak_periods(self, df: pd.DataFrame) -> Dict:
        """Identify peak revenue periods"""
        hourly_revenue = df.groupby('hour')['daily_revenue'].sum()
        
        peak_hours = hourly_revenue.nlargest(3).index.tolist()
        
        # Day of week analysis
        df['date'] = pd.to_datetime(df['date'])
        df['day_of_week'] = df['date'].dt.day_name()
        daily_revenue = df.groupby('day_of_week')['daily_revenue'].sum()
        peak_days = daily_revenue.nlargest(3).index.tolist()
        
        return {
            'peak_hours': [int(h) for h in peak_hours],
            'peak_days': peak_days,
            'peak_hour_revenue': float(hourly_revenue.max()),
            'off_peak_hour_revenue': float(hourly_revenue.min())
        }

    def _predict_customer_lifetime_value(self, df: pd.DataFrame) -> Dict:
        """Predict customer lifetime value"""
        if df.empty:
            return {'error': 'No customer data available'}
        
        avg_services = df['total_services'].mean()
        avg_spending = df['total_spent'].mean()
        
        # Simple CLV calculation
        avg_clv = avg_spending
        retention_factor = 0.8  # Assume 80% retention
        projected_clv = avg_clv * (1 + retention_factor)
        
        return {
            'average_current_value': float(avg_spending),
            'average_services': float(avg_services),
            'projected_lifetime_value': float(projected_clv),
            'high_value_threshold': float(df['total_spent'].quantile(0.75))
        }

    def _identify_at_risk_customers(self, df: pd.DataFrame) -> List[Dict]:
        """Identify customers at risk of churning"""
        if df.empty:
            return []
        
        # Customers who haven't used service in 90+ days
        df['last_service_date'] = pd.to_datetime(df['last_service_date'])
        df['days_since_service'] = (pd.Timestamp.now() - df['last_service_date']).dt.days
        
        at_risk = df[df['days_since_service'] > 90].copy()
        at_risk = at_risk.sort_values('total_spent', ascending=False)
        
        return at_risk[['id', 'first_name', 'last_name', 'days_since_service', 'total_spent']].head(10).to_dict('records')

    # Additional helper methods would be implemented here...

def main():
    """Main function for command line usage"""
    import argparse

    parser = argparse.ArgumentParser(description='Analyze roadside assistance data')
    parser.add_argument('--analysis', choices=['demand', 'drivers', 'customers', 'revenue'],
                       default='demand', help='Type of analysis to perform')
    parser.add_argument('--days', type=int, default=30, help='Number of days to analyze')
    parser.add_argument('--output', help='Output file for results (JSON format)')

    args = parser.parse_args()

    analyzer = DataAnalyzer()

    try:
        if args.analysis == 'demand':
            results = analyzer.analyze_service_demand(args.days)
        elif args.analysis == 'drivers':
            results = analyzer.analyze_driver_performance(args.days)
        elif args.analysis == 'customers':
            results = analyzer.analyze_customer_behavior(args.days)
        elif args.analysis == 'revenue':
            results = analyzer.analyze_revenue_trends(args.days)

        if 'error' in results:
            print(f"Analysis failed: {results['error']}")
            return 1

        # Output results
        if args.output:
            import json
            with open(args.output, 'w') as f:
                json.dump(results, f, indent=2, default=str)
            print(f"Results saved to: {args.output}")
        else:
            print(json.dumps(results, indent=2, default=str))

    except Exception as e:
        print(f"Analysis failed: {e}")
        return 1

    return 0

if __name__ == "__main__":
    exit(main())
