import React from 'react';

import { makeStyles, Typography } from '@material-ui/core';

import { path, isNil } from 'ramda';
import Shortcuts from './Shortcuts';
import hasDefinedValues from '../../../hasDefinedValues';
import { labelHost } from '../../../translatedLabels';
import { TabProps } from '..';
import { ResourceUris } from '../../../models';

const useStyles = makeStyles((theme) => {
  return {
    container: {
      display: 'grid',
      gridGap: theme.spacing(1),
    },
  };
});

const ShortcutsTab = ({ details }: TabProps): JSX.Element | null => {
  const classes = useStyles();

  if (isNil(details)) {
    // TODO Loading skeleton
    return null;
  }

  const { uris: resourceUris } = details.links;
  const parentUris = path(['parent', 'links', 'uris'], details) as ResourceUris;

  return (
    <div className={classes.container}>
      {hasDefinedValues(resourceUris) && <Shortcuts uris={resourceUris} />}
      {hasDefinedValues(parentUris) && (
        <>
          <Typography variant="h6">{labelHost}</Typography>
          <Shortcuts uris={parentUris} />
        </>
      )}
    </div>
  );
};

export default ShortcutsTab;
