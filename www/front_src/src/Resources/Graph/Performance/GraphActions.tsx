import * as React from 'react';

import { isNil } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useHistory } from 'react-router';

import { makeStyles, Menu, MenuItem } from '@material-ui/core';
import SaveAsImageIcon from '@material-ui/icons/SaveAlt';
import LaunchIcon from '@material-ui/icons/Launch';

import {
  ContentWithCircularLoading,
  useLocaleDateTimeFormat,
  IconButton,
} from '@centreon/ui';

import {
  labelAsDisplayed,
  labelExportToPng,
  labelMediumSize,
  labelPerformancePage,
  labelSmallSize,
} from '../../translatedLabels';
import { CustomTimePeriod } from '../../Details/tabs/Graph/models';
import { TimelineEvent } from '../../Details/tabs/Timeline/models';
import memoizeComponent from '../../memoizedComponent';

import exportToPng from './ExportableGraphWithTimeline/exportToPng';

interface Props {
  customTimePeriod?: CustomTimePeriod;
  performanceGraphRef: React.MutableRefObject<HTMLDivElement | null>;
  resourceName: string;
  resourceParentName?: string;
  timeline?: Array<TimelineEvent>;
}

const useStyles = makeStyles((theme) => ({
  buttonGroup: {
    columnGap: `${theme.spacing(1)}px`,
    display: 'flex',
    flexDirection: 'row',
  },
  buttonLink: {
    background: 'transparent',
    border: 'none',
  },
}));

const GraphActions = ({
  customTimePeriod,
  resourceParentName,
  resourceName,
  timeline,
  performanceGraphRef,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [menuAnchor, setMenuAnchor] = React.useState<Element | null>(null);
  const [exporting, setExporting] = React.useState<boolean>(false);
  const { format } = useLocaleDateTimeFormat();
  const history = useHistory();

  const openSizeExportMenu = (event: React.MouseEvent): void => {
    setMenuAnchor(event.currentTarget);
  };
  const closeSizeExportMenu = (): void => {
    setMenuAnchor(null);
  };
  const goToPerformancePage = (): void => {
    const startTimestamp = format({
      date: customTimePeriod?.start as Date,
      formatString: 'X',
    });
    const endTimestamp = format({
      date: customTimePeriod?.end as Date,
      formatString: 'X',
    });

    const urlParameters = (): string => {
      const params = new URLSearchParams({
        end: endTimestamp,
        mode: '0',
        start: startTimestamp,
        svc_id: `${resourceParentName};${resourceName}`,
      });

      return params.toString();
    };

    history.push(`/main.php?p=204&${urlParameters()}`);
  };

  const convertToPng = (ratio: number): void => {
    setMenuAnchor(null);
    setExporting(true);
    exportToPng({
      element: performanceGraphRef.current as HTMLElement,
      ratio,
      title: `${resourceName}-performance`,
    }).finally(() => {
      setExporting(false);
    });
  };

  return (
    <div className={classes.buttonGroup}>
      <ContentWithCircularLoading
        alignCenter={false}
        loading={exporting}
        loadingIndicatorSize={16}
      >
        <>
          <IconButton
            disableTouchRipple
            ariaLabel={t(labelPerformancePage)}
            className={classes.buttonLink}
            color="primary"
            data-testid={labelPerformancePage}
            size="small"
            title={t(labelPerformancePage)}
            onClick={goToPerformancePage}
          >
            <LaunchIcon style={{ fontSize: 18 }} />
          </IconButton>
          <IconButton
            disableTouchRipple
            ariaLabel={t(labelExportToPng)}
            data-testid={labelExportToPng}
            disabled={isNil(timeline)}
            title={t(labelExportToPng)}
            onClick={openSizeExportMenu}
          >
            <SaveAsImageIcon style={{ fontSize: 18 }} />
          </IconButton>
          <Menu
            keepMounted
            anchorEl={menuAnchor}
            open={Boolean(menuAnchor)}
            onClose={closeSizeExportMenu}
          >
            <MenuItem
              data-testid={labelAsDisplayed}
              onClick={(): void => convertToPng(1)}
            >
              {t(labelAsDisplayed)}
            </MenuItem>
            <MenuItem
              data-testid={labelMediumSize}
              onClick={(): void => convertToPng(0.75)}
            >
              {t(labelMediumSize)}
            </MenuItem>
            <MenuItem
              data-testid={labelSmallSize}
              onClick={(): void => convertToPng(0.5)}
            >
              {t(labelSmallSize)}
            </MenuItem>
          </Menu>
        </>
      </ContentWithCircularLoading>
    </div>
  );
};

const MemoizedGraphActions = memoizeComponent<Props>({
  Component: GraphActions,
  memoProps: [
    'customTimePeriod',
    'resourceParentName',
    'resourceName',
    'timeline',
    'performanceGraphRef',
  ],
});

export default MemoizedGraphActions;
