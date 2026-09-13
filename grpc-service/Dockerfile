FROM python:3.12-slim

WORKDIR /app

# Install dependensi Python
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy proto dan generate kode Python dari monitoring.proto
COPY proto/ ./proto/
RUN python -m grpc_tools.protoc \
    -I./proto \
    --python_out=. \
    --grpc_python_out=. \
    ./proto/monitoring.proto

# Copy seluruh source code
COPY . .

EXPOSE 50051

CMD ["python", "server.py"]
