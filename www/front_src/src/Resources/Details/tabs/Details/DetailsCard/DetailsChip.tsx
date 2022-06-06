import { useState, useCallback } from 'react';

import { useTranslation } from 'react-i18next';
import clsx from 'clsx';
import { useUpdateAtom } from 'jotai/utils';

import makeStyles from '@mui/styles/makeStyles';
import SettingsIcon from '@mui/icons-material/Settings';
import FilterListIcon from '@mui/icons-material/FilterList';
import IconButton from '@mui/material/IconButton';
import { Chip, Grid, Tooltip, Typography } from '@mui/material';

import { CriteriaNames } from '../../../../Filter/Criterias/models';
import { setCriteriaAndNewFilterDerivedAtom } from '../../../../Filter/filterAtoms';
import { Group, Category } from '../../../models';

const useStyles = makeStyles((theme) => ({
  chip: {
    alignSelf: 'center',
    display: 'flex',
  },
  chipAction: {
    gridArea: '1/1',
    maxWidth: theme.spacing(14),
    minWidth: theme.spacing(8),
    overflow: 'hidden',
  },
  chipHover: {
    backgroundColor: theme.palette.primary.main,
    display: 'flex',
    gap: theme.spacing(0.25),
    gridArea: '1/1',
  },
  chipIconColor: {
    color: theme.palette.common.white,
  },
  chipLabel: {
    display: 'grid',
    justifyItems: 'center',
    minWidth: theme.spacing(7),
    overflow: 'hidden',
  },
  chipLabelColor: { color: 'transparent' },
}));

interface Props {
  metaResourceType: Group | Category;
  type: CriteriaNames;
}

const DetailsChip = ({ metaResourceType, type }: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();
  const [isHovered, setIsHovered] = useState<boolean>(false);

  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

  const mouseEnter = (): void => {
    setIsHovered(true);
  };

  const mouseLeave = (): void => {
    setIsHovered(false);
  };

  const filterByMetaResourceType = useCallback((): void => {
    setCriteriaAndNewFilter({
      name: type,
      value: [metaResourceType],
    });
  }, [metaResourceType, type]);

  const configureMetaResourceType = useCallback((): void => {
    window.location.href = metaResourceType.configuration_uri as string;
  }, [metaResourceType]);

  const { name, id } = metaResourceType;

  return (
    <Grid item className={classes.chip} key={id}>
      <Chip
        aria-label={`${name} Chip`}
        color="primary"
        label={
          <div className={classes.chipLabel}>
            <Tooltip title={name}>
              <Typography
                className={clsx(
                  classes.chipAction,
                  isHovered ? classes.chipLabelColor : '',
                )}
                variant="body2"
              >
                {name}
              </Typography>
            </Tooltip>
            {isHovered && (
              <Grid className={classes.chipHover}>
                <IconButton
                  aria-label={`${name} Filter`}
                  className={classes.chipIconColor}
                  size="small"
                  title={t(name)}
                  onClick={filterByMetaResourceType}
                >
                  <FilterListIcon fontSize="small" />
                </IconButton>
                <IconButton
                  aria-label={`${name} Configure`}
                  className={classes.chipIconColor}
                  size="small"
                  title={t(name)}
                  onClick={configureMetaResourceType}
                >
                  <SettingsIcon fontSize="small" />
                </IconButton>
              </Grid>
            )}
          </div>
        }
        onMouseEnter={mouseEnter}
        onMouseLeave={mouseLeave}
      />
    </Grid>
  );
};

export default DetailsChip;
