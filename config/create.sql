CREATE TABLE DEALERSHIP_ADDRESS (
    StreetAddress   VARCHAR(30) NOT NULL,
    City            VARCHAR(20) NOT NULL,
    Zipcode         VARCHAR(5)  NOT NULL,
    Finance_Option  VARCHAR(30) NOT NULL,
    CONSTRAINT DEAL_ADDR_PK 
        PRIMARY KEY (StreetAddress, City, Zipcode, Finance_Option),
    CONSTRAINT CHECK_ZIP 
        CHECK (Zipcode GLOB '[0-9][0-9][0-9][0-9][0-9]')
);

CREATE TABLE Dealership (
    DealershipID    INT         NOT NULL,
    DealerName      VARCHAR(30) NOT NULL,
    StreetAddress   VARCHAR(30) NOT NULL,
    City            VARCHAR(20) NOT NULL,
    Zipcode         VARCHAR(5)  NOT NULL,
    CONSTRAINT DEALPK
        PRIMARY KEY (DealershipID),
    CONSTRAINT DEAL_ADDR_FK
        FOREIGN KEY (StreetAddress, City, Zipcode) 
        REFERENCES DEALERSHIP_ADDRESS(StreetAddress, City, Zipcode)
            ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE MODEL (
    Model           VARCHAR(20)     NOT NULL,
    Make            VARCHAR(20)     NOT NULL,
    CONSTRAINT MODEL_PK 
        PRIMARY KEY (Model)
);

CREATE TABLE MODEL_YEAR (
    Model           VARCHAR(20)     NOT NULL,
    Vehicle_Year    INT             NOT NULL,
    NumSeats        INT             NOT NULL,
    TransType       VARCHAR(20),
    EngineSize      DECIMAL(3, 2),
    FuelType        VARCHAR(20),
    CONSTRAINT MODEL_YEAR_PK 
        PRIMARY KEY (Model, Vehicle_Year),
    CONSTRAINT MODEL_YEAR_FK 
        FOREIGN KEY (Model) REFERENCES MODEL(Model)
            ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT CHECK_YEAR 
        CHECK (Vehicle_Year BETWEEN 1886 AND 2100),
    CONSTRAINT CHECK_SEATS 
        CHECK (NumSeats >= 0),
    CONSTRAINT CHECK_TRANSTYPE 
        CHECK (TransType IN ('Auto', 'Manual'))
);

CREATE TABLE VEHICLE (
    VIN             INT             NOT NULL,
    DealershipID    INT             NOT NULL,
    Model           VARCHAR(20)     NOT NULL,
    Vehicle_Year    INT             NOT NULL,
    Color           VARCHAR(10)     NOT NULL,
    NumOwners       INT,         
    Mileage         INT             NOT NULL,
    Price           DECIMAL(9, 2)   NOT NULL,
    EMC             DECIMAL(6, 2),   
    CONSTRAINT VEHICLE_PK 
        PRIMARY KEY (VIN),
    CONSTRAINT VEHICLE_FK_DEALER 
        FOREIGN KEY (DealershipID) REFERENCES DEALERSHIP(DealershipID)
            ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT VEHICLE_FK_MODEL_YEAR 
        FOREIGN KEY (Model, Vehicle_Year) REFERENCES MODEL_YEAR(Model, Vehicle_Year)
            ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT CHECK_NUMOWN 
        CHECK (NumOwners IS NULL OR NumOwners >= 0),
    CONSTRAINT CHECK_MILES 
        CHECK (Mileage >= 0),
    CONSTRAINT CHECK_PRICE 
        CHECK (Price >= 0)
);

CREATE TABLE CAR(
    VIN         INT     NOT NULL,
    FEATURES    VARCHAR(50),
    CONSTRAINT CARPK
        PRIMARY KEY (VIN),
    CONSTRAINT CARFK
        FOREIGN KEY (VIN) REFERENCES VEHICLE(VIN)
            ON DELETE CASCADE  ON UPDATE CASCADE
);


CREATE TABLE TRUCK(
    VIN         INT         NOT NULL,
    DriveTrain  VARCHAR(10) NOT NULL,
    TowCapacity INT         NOT NULL,
    CONSTRAINT TRUCKPK
        PRIMARY KEY (VIN),
    CONSTRAINT TRUCKFK
        FOREIGN KEY (VIN) REFERENCES VEHICLE(VIN)
            ON DELETE CASCADE   ON UPDATE CASCADE,
    CONSTRAINT CHECK_DRIVETRAIN CHECK (DriveTrain IN ('4WD', 'AWD', 'FWD', 'RWD'))
);

CREATE TABLE MOTORCYCLE(
    VIN     INT NOT NULL,
    CC      INT NOT NULL,
    CONSTRAINT MOTORCYCLEPK
        PRIMARY KEY (VIN),
    CONSTRAINT MOTORCYCLEFK
        FOREIGN KEY (VIN) REFERENCES VEHICLE(VIN)
            ON DELETE CASCADE  ON UPDATE CASCADE
);

CREATE TABLE CUSTOMER(
    CustomerID      INT         NOT NULL,
    CustomerName    VARCHAR(20), 
    Email           VARCHAR(30),
    CONSTRAINT CUSTPK
        PRIMARY KEY (CustomerID)
);

CREATE TABLE SALES_AGREEMENT(
    CustomerID      INT             NOT NULL,
    DealershipID    INT             NOT NULL,
    VIN             INT             NOT NULL,
    Sale_Date       TEXT,
    Final_Price     DECIMAL(9, 2)   NOT NULL,
    CONSTRAINT SALESPK
        PRIMARY KEY (CustomerID, DealershipID, VIN),
    CONSTRAINT SALES_CUSTFK
        FOREIGN KEY (CustomerID) REFERENCES CUSTOMER(CustomerID)
            ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT SALES_DEALFK
        FOREIGN KEY (DealershipID) REFERENCES DEALERSHIP(DealershipID)
            ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT SALES_VEHICLEFK
        FOREIGN KEY (VIN) REFERENCES VEHICLE(VIN)
            ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT CHECK_SALEDATE CHECK (date(Sale_Date) IS NOT NULL),
    CONSTRAINT CHECK_PRICE CHECK (Final_Price>=0)
);

CREATE TABLE WISHLIST(
    CustomerID  INT     NOT NULL,
    VIN         INT     NOT NULL,
    WishDate    TEXT,
    CONSTRAINT WISHPK
        PRIMARY KEY (CustomerID, VIN),
    CONSTRAINT WISH_CUSTTFK
        FOREIGN KEY (CustomerID) REFERENCES CUSTOMER(CustomerID)
            ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT WISH_VEHICLEFK
        FOREIGN KEY (VIN) REFERENCES VEHICLE(VIN)
            ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT CHECK_WISHDATE CHECK (date(WishDate) IS NOT NULL)
);


