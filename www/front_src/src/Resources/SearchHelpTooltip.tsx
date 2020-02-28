import React from 'react';

import { Tooltip } from '@material-ui/core';
import { HelpOutline as IconHelp } from '@material-ui/icons';

import {
  labelSearchOnFields,
  labelSearchByHostName,
  labelUsePartialQuery,
  labelSearchByServiceStartingWith,
  labelSearchByHostAliasEndingWith,
} from './translatedLabels';

const Content = (): JSX.Element => (
  <>
    <p>{labelSearchOnFields}</p>
    <ul>
      <li>host.name</li>
      <li>host.alias</li>
      <li>host.address</li>
      <li>service.description</li>
    </ul>
    <p>{`${labelSearchByHostName} host.name:hostname`}</p>
    <p>{labelUsePartialQuery}</p>
    <ul>
      <li>{`${labelSearchByServiceStartingWith} service.description:centreon%`}</li>
      <li>{`${labelSearchByHostAliasEndingWith} host.alias:%-server`}</li>
    </ul>
  </>
);

const SearchHelpTooltip = (): JSX.Element => (
  <Tooltip title={<Content />}>
    <IconHelp />
  </Tooltip>
);

export default SearchHelpTooltip;
