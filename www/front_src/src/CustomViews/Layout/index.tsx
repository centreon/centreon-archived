import { FC, useEffect } from 'react';

import { Responsive } from '@visx/visx';
import GridLayout, { WidthProvider } from 'react-grid-layout';
import { useAtom, useAtomValue, useSetAtom } from 'jotai';
import { always, cond, equals, find, gt, map, propEq, T } from 'ramda';

import { Responsive as ResponsiveHeight } from '@centreon/ui';

import {
  breakpointAtom,
  changeLayoutDerivedAtom,
  columnsAtom,
  getBreakpoint,
  isEditingAtom,
  layoutByBreakpointDerivedAtom,
  responsiveLayoutAtom,
} from '../atoms';

import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';

import EditionGrid from './EditionGrid';
import Widget from './Widget';

const ReactGridLayout = WidthProvider(GridLayout);

const Layout: FC = () => {
  const [layout, setLayout] = useAtom(layoutByBreakpointDerivedAtom);
  const [columns, setColumn] = useAtom(columnsAtom);
  const [breakpoint, setBreakpoint] = useAtom(breakpointAtom);
  const isEditing = useAtomValue(isEditingAtom);
  const responsiveLayout = useAtomValue(responsiveLayoutAtom);
  const changeWidgetsLayout = useSetAtom(changeLayoutDerivedAtom);

  const changeLayout = (newLayout): void => {
    setLayout(
      map(({ i, ...other }) => {
        const widget = find(propEq('i', i), layout);

        return {
          ...other,
          i,
          widgetConfiguration: widget?.widgetConfiguration,
        };
      }, newLayout),
    );
  };

  const resize = (): void => {
    const newBreakpoint = getBreakpoint(window.innerWidth);

    if (equals(breakpoint, newBreakpoint)) {
      return;
    }

    changeWidgetsLayout({ breakpoint: newBreakpoint });
  };

  useEffect(() => {
    resize();
  }, []);

  useEffect(() => {
    window.addEventListener('resize', resize);

    return () => {
      window.removeEventListener('resize', resize);
    };
  }, [breakpoint]);

  return (
    <ResponsiveHeight>
      <Responsive.ParentSize>
        {({ width, height }): JSX.Element => (
          <>
            {isEditing && <EditionGrid height={height} width={width} />}
            <ReactGridLayout
              cols={columns}
              containerPadding={[0, 0]}
              layout={layout}
              resizeHandles={['s', 'e', 'se']}
              rowHeight={30}
              width={width}
              onLayoutChange={changeLayout}
            >
              {layout.map(({ i, widgetConfiguration }) => {
                return (
                  <div key={i}>
                    <Widget key={i} path={widgetConfiguration.path} title={i} />
                  </div>
                );
              })}
            </ReactGridLayout>
          </>
        )}
      </Responsive.ParentSize>
    </ResponsiveHeight>
  );
};

export default Layout;
