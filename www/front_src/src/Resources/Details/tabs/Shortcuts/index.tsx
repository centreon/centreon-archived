import React from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles } from '@material-ui/core';

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
  };
});

const ShortcutsTab = ({ details }: TabProps): JSX.Element | null => {
  const classes = useStyles();

  const { t } = useTranslation();

  if (isNil(details)) {
    // TODO Loading skeleton
    return null;
  }

  const { uris: resourceUris } = details.links;
  const parentUris = path(['parent', 'links', 'uris'], details) as ResourceUris;

  const isService = hasDefinedValues(parentUris);

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
