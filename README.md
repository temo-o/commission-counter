
# Commission Counter

## Project Overview
This application calculates commission fees for deposits and withdrawals based on specified business rules. It handles multiple currencies, caches exchange rates for optimal performance, and ensures accurate fee calculations.

---

## Prerequisites
Before running the project, ensure you have the following installed:

**Docker**  
**Docker Compose**  

---

## Setup 

### 1. **Clone the Repository**
```
git clone git@github.com:temo-o/commission-counter.git
cd commission-counter
```

---

### 2. **Build the Docker Container**
Run the following command to build the Docker container:
```
docker-compose up --build
```

---

### 3. **Install Dependencies**
```
docker exec -it commission-counter-app-1 composer install
```

---

### 4. **Run the Commission Calculation Command**
To calculate commissions using a provided CSV file:

```
docker exec -it commission-counter-app-1 php bin/console commission-counter:calculate input.csv
```

Replace `input.csv` with the path to your input file.

**P.S.** input.csv file provided is just for testing, there should not be such file included in the repo directly

---

## Running Tests
To run the automated tests:

```
docker exec -it commission-counter-app-1 php bin/phpunit
```


---

**P.S.** This repo includes .env file with variables that should not be here. In case of using this repo, please remove or obstruct the sensitive values

---

## How to Add a New Commission Strategy

#### 1. **Create a new class inside ```src/Service/Strategy/``` that implements ```CommissionStrategyInterface```**
#### 2. **Register the Strategy in ```config/services.yaml```**

example:
```
services:
    App\Service\Strategy\NewCommissionStrategy:
        tags: ['app.commission_strategy']
```

Symfony's Dependency Injection will automatically inject the new strategy into ```CommissionStrategyFactory``` - no further changes required!