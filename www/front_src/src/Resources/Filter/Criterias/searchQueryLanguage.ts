// import { keys, prop } from 'ramda';

// import { SelectEntry } from '@centreon/centreon-frontend/packages/centreon-ui/src';

// import { selectableCriterias } from './models';

// const buildOptions = (options: Array<SelectEntry>) => {
//   const optionsExpression = options.map(prop('id'));
//   const joinedExpression = optionsExpression.join('|');

//   return `[${joinedExpression}]+(,[${joinedExpression}]+)*`;
// };

// const buildCriteriaRegexps = () => {
//   const keyas = keys(selectableCriterias);

//   return keyas.map((key) => {
//     const criteria = selectableCriterias[key];

//     const { options } = criteria;

//     const optionExpression = options
//       ? buildOptions(options)
//       : `[0-9]+(,[0-9]+)*`;

//     return `${key}:${optionExpression}`;
//   });
// };

// const parse = (search: string) => {

// };
