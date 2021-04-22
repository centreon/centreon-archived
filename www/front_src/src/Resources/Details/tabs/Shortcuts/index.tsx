import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { path, isNil } from 'ramda';

import { makeStyles, Paper } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import hasDefinedValues from '../../../hasDefinedValues';
import { labelHost } from '../../../translatedLabels';
import { TabProps } from '..';
import { ResourceUris } from '../../../models';

import ShortcutsSection from './ShortcutsSection';

const useStyles = makeStyles((theme) => {
  return {
    container: {
      display: 'grid',
      gridGap: theme.spacing(1),
    },
    loadingSkeleton: {
      display: 'flex',
      flexDirection: 'column',
      height: 120,
      justifyContent: 'space-between',
      padding: theme.spacing(2),
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

const ShortcutsTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const resourceUris = path<ResourceUris>(
    ['links', 'uris'],
    details,
  ) as ResourceUris;
  const parentUris = path<ResourceUris>(['parent', 'links', 'uris'], details);

  const hasParentUris = parentUris && hasDefinedValues(parentUris);

  const resourceTitleByType = {
    host: 'Host',
    metaservice: 'Meta service',
    service: 'Service',
  };

  if (isNil(details)) {
    return <LoadingSkeleton />;
  }

  const resourceTitle = t(resourceTitleByType[details.type]);

  return (
    <div className={classes.container}>
      <ShortcutsSection title={resourceTitle} uris={resourceUris} />
      {hasParentUris && (
        <ShortcutsSection
          title={t(labelHost)}
          uris={parentUris as ResourceUris}
        />
      )}
    </div>
  );
};

export default ShortcutsTab;
