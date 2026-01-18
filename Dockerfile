FROM php:8.2-cli

# Set the folder to the current directory
WORKDIR /app

# Copy YOUR modified files (don't download fresh ones)
COPY . .

# Start the PHP server on port 10000
CMD php -S 0.0.0.0:10000
