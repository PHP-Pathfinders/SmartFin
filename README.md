# SmartFin

Welcome to the SmartFin project! This repository contains the source code for the SmartFin application.

## Getting Started

Follow the steps below to set up and start the project on your local machine.

### Prerequisites

Ensure you have the following tools installed on your system:

- Git
- Docker
- Docker Compose

### Installation

1. **Clone the repository**:
    ```bash
    git clone git@github.com:PHP-Pathfinders/SmartFin.git
    ```

2. **Navigate to the project directory**:
    ```bash
    cd SmartFin
    ```

3. **Build and start the Docker containers**:
    ```bash
    docker compose up --build -d
    ```

4. **Access the PHP container**:
    ```bash
    docker exec -it php83-container bash
    ```

5. **Install the project dependencies**:
    ```bash
    composer install
    ```

## Usage

After completing the above steps, the SmartFin application should be up and running. You can access the application via your web browser or interact with it as required.

## Contributing

We welcome contributions to improve the SmartFin project. If you have any suggestions, bug reports, or feature requests, please create an issue or submit a pull request.

---

Thank you for using SmartFin!

