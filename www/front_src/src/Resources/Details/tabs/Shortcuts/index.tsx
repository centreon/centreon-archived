import React from 'react';

import { makeStyles } from '@material-ui/core';

import { ResourceLinks } from '../../../models';
import Shortcuts from './Shortcuts';
import hasDefinedValues from '../../../hasDefinedValues';

const useStyles = makeStyles((theme) => {
  return {
    container: {
      display: 'grid',
      gridGap: theme.spacing(1),
      padding: theme.spacing(1),
    },
  };
});

interface Props {
  links: ResourceLinks;
}

const ShortcutsTab = ({ links }: Props): JSX.Element => {
  const classes = useStyles();

  const { uris } = links;
  const { resource: resourceUris } = uris;
  const { parent: parentUris } = uris;

  return (
    <div className={classes.container}>
      {hasDefinedValues(resourceUris) && <Shortcuts uris={resourceUris} />}
      {hasDefinedValues(parentUris) && <Shortcuts uris={parentUris} />}
    </div>
  );
};

export default ShortcutsTab;
