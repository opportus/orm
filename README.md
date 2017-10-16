## About
I've written this ORM as a school project. I maintain and develop it as I use it for my personnal projects.<br />

It implements the following design patterns:

 - Data gateway (a default PDO_MYSQL Adapter implementing GatewayInterface)
 - Data mapper
 - Repository
 - Model factory
 - Domain model

### Phylosophy

KISS > Flexibility > Extensibility

### Important Notes

Note that this ORM is still in pre-alpha, so:

- It doesn't support advanced mapping yet, meaning that at this time, you can only create entities whose data are all in the same table
- It may include new features and possibly heavy changes anytime soon

Note also that PHP versions lower than the **7.0** *might* never be supported...

## Contributing

Constructive issues/PRs of any type are more than welcome !

## Doc

Will work on the doc when the ORM will get stable enough...
