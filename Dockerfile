FROM python:3.11-slim

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    gcc \
    && rm -rf /var/lib/apt/lists/*

# Copy requirements first for better caching
COPY site_demo/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy application code
COPY site_demo/ .

# Expose port
EXPOSE 8000

# Start command - Railway sets PORT env var
CMD uvicorn app.main:app --host 0.0.0.0 --port ${PORT:-8000}
