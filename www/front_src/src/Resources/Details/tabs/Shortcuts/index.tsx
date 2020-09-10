import React from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles, Paper } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { path, isNil } from 'ramda';
import ShortcutsSection from './Shortcuts';
import hasDefinedValues from '../../../hasDefinedValues';
import { labelHost, labelService } from '../../../translatedLabels';
import { TabProps } from '..';
import { ResourceUris } from '../../../models';

const useStyles = makeStyles((theme) => {
  return {
    container: {
      display: 'grid',
      gridGap: theme.spacing(1),
    },
    loadingSkeleton: {
      padding: theme.spacing(2),
      height: 120,
      display: 'flex',
      flexDirection: 'column',
      justifyContent: 'space-between',
    },
  };
});

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <Paper className={classes.loadingSkeleton}>
      <Skeleton width={175} />
      <Skeleton width={175} />
      <Skeleton width={170} />
    </Paper>
  );
};

const ShortcutsTab = ({ details }: TabProps): JSX.Element | null => {
  const classes = useStyles();

  const { t } = useTranslation();

  const resourceUris = path(['links', 'uris'], details);
  const parentUris = path(['parent', 'links', 'uris'], details) as ResourceUris;

  const isService = hasDefinedValues(parentUris);

  if (isNil(details)) {
    return <LoadingSkeleton />;
  }

  return (
    <div className={classes.container}>
      <ShortcutsSection
        title={t(isService ? labelService : labelHost)}
        uris={resourceUris}
      />
      {isService && <ShortcutsSection title={t(labelHost)} uris={parentUris} />}
    </div>
  );
};

export default ShortcutsTab;
